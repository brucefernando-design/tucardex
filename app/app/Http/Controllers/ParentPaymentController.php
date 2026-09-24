<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Setting;
use App\Services\Tenancy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ParentPaymentController extends Controller
{
    /**
     * Muestra la pantalla de checkout para liquidar una colegiatura o pago pendiente.
     * Protegido mediante token opaco único en la URL (sin IDs numéricos predecibles).
     */
    public function checkout(Payment $payment): View|RedirectResponse
    {
        $this->resolveTenantAndValidate($payment);

        $user = auth()->user();
        if (! $this->canAccessPayment($user, $payment)) {
            abort(403, 'No tienes autorización para acceder a esta colegiatura.');
        }

        if ($payment->status === 'pagado') {
            return redirect()->route('parent.payments.success', $payment)
                ->with('info', 'Esta colegiatura ya se encuentra pagada.');
        }

        $payment->load('student.course');
        $student = $payment->student;
        $setting = Setting::current();

        return view('payments.checkout', compact('payment', 'student', 'setting'));
    }

    /**
     * Inicia el proceso de cobro con Mercado Pago (crea preferencia en la API oficial).
     */
    public function mercadoPago(Payment $payment): RedirectResponse
    {
        $this->resolveTenantAndValidate($payment);

        $user = auth()->user();
        if (! $this->canAccessPayment($user, $payment)) {
            abort(403, 'No tienes autorización para acceder a esta colegiatura.');
        }

        if ($payment->status === 'pagado') {
            return redirect()->route('parent.payments.success', $payment)
                ->with('info', 'Esta colegiatura ya se encuentra pagada.');
        }

        $setting = Setting::current();

        // Si el colegio tiene credenciales reales de Mercado Pago configuradas
        if ($setting->mercadopago_enabled && ! empty($setting->mercadopago_access_token)) {
            try {
                $response = Http::withToken($setting->mercadopago_access_token)
                    ->timeout(10)
                    ->post('https://api.mercadopago.com/checkout/preferences', [
                        'items' => [
                            [
                                'id' => (string) $payment->id,
                                'title' => $payment->concept . ' - ' . $payment->student->full_name,
                                'description' => 'Colegiatura ' . ($setting->school_name ?? 'Colegio'),
                                'quantity' => 1,
                                'currency_id' => 'MXN',
                                'unit_price' => (float) $payment->amount,
                            ],
                        ],
                        'payer' => [
                            'name' => $user ? $user->name : ($payment->student->guardian_name ?? $payment->student->full_name),
                            'email' => $user ? $user->email : ($payment->student->guardian_email ?? 'contacto@' . (parse_url(config('app.url'), PHP_URL_HOST) ?? 'tucardex.com')),
                        ],
                        'back_urls' => [
                            'success' => route('parent.payments.return', ['payment' => $payment->token, 'status' => 'success']),
                            'failure' => route('parent.payments.return', ['payment' => $payment->token, 'status' => 'failure']),
                            'pending' => route('parent.payments.return', ['payment' => $payment->token, 'status' => 'pending']),
                        ],
                        'auto_return' => 'approved',
                        'external_reference' => (string) $payment->token,
                        'notification_url' => route('webhooks.mercadopago', ['school' => $payment->school_id]),
                        'statement_descriptor' => Str::limit($setting->school_name ?? 'COLEGIO', 15, ''),
                    ]);

                if ($response->successful() && $response->json('init_point')) {
                    return redirect()->away($response->json('init_point'));
                }

                logger()->error('Error creando preferencia MP:', $response->json() ?? []);
                return back()->with('error', 'No fue posible iniciar Mercado Pago: ' . ($response->json('message') ?? 'Verifica las credenciales del colegio.'));
            } catch (\Throwable $e) {
                logger()->error('Excepción Mercado Pago: ' . $e->getMessage());
                return back()->with('error', 'Error al comunicar con la pasarela de pagos.');
            }
        }

        // Si no tiene credenciales configuradas, orientar al usuario a transferencia SPEI
        return back()->with('error', 'El cobro con tarjeta en línea no está disponible en este momento. Por favor realiza tu pago por transferencia SPEI o en ventanilla escolar.');
    }

    /**
     * Retorno del padre tras completar el flujo en Mercado Pago.
     * Blindado: Jamás acredita saldo solo por query string; exige confirmación por Webhook o verificación oficial de API.
     */
    public function returnCallback(Request $request, Payment $payment): RedirectResponse
    {
        $this->resolveTenantAndValidate($payment);

        $status = $request->query('collection_status', $request->query('status'));
        $paymentId = $request->query('payment_id');

        // 1. Si el pago ya fue conciliado por el Webhook en tiempo real
        if ($payment->status === 'pagado') {
            return redirect()->route('parent.payments.success', $payment)
                ->with('success', '¡Tu pago ha sido acreditado exitosamente por Mercado Pago!');
        }

        // 2. Si viene payment_id, consultar directamente a la API oficial de Mercado Pago
        if (($status === 'approved' || $status === 'success') && $paymentId) {
            $setting = Setting::current();
            if ($setting && ! empty($setting->mercadopago_access_token)) {
                try {
                    $mpResponse = Http::withToken($setting->mercadopago_access_token)
                        ->timeout(10)
                        ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

                    if ($mpResponse->successful()) {
                        $paymentData = $mpResponse->json();
                        $mpStatus = $paymentData['status'] ?? null;
                        $externalRef = $paymentData['external_reference'] ?? null;

                        if ($mpStatus === 'approved' && $externalRef === $payment->token) {
                            $methodType = $paymentData['payment_type_id'] ?? 'tarjeta';
                            $method = match ($methodType) {
                                'bank_transfer' => 'transferencia',
                                'ticket' => 'efectivo',
                                default => 'tarjeta',
                            };

                            $payment->update([
                                'status' => 'pagado',
                                'paid_date' => now(),
                                'method' => $method,
                                'remarks' => "Acreditado vía verificación oficial API Mercado Pago #{$paymentId}",
                            ]);

                            return redirect()->route('parent.payments.success', $payment)
                                ->with('success', '¡Tu pago ha sido confirmado exitosamente por Mercado Pago!');
                        }
                    }
                } catch (\Throwable $e) {
                    logger()->error('Error verificando pago en retorno MP: ' . $e->getMessage());
                }
            }
        }

        // 3. Si aún no está acreditado, informar al usuario sin alterar el estado en BD
        if ($status === 'approved' || $status === 'success' || $status === 'pending') {
            return redirect()->route('parent.payments.checkout', $payment)
                ->with('info', 'Tu pago está siendo procesado por la pasarela bancaria. En cuanto Mercado Pago confirme la acreditación a tu colegio, tu recibo estará disponible automáticamente.');
        }

        return redirect()->route('parent.payments.checkout', $payment)
            ->with('error', 'El pago fue cancelado o no completado por el banco emisor.');
    }

    /**
     * Pantalla de éxito con confirmación y botón de descarga de recibo oficial en PDF.
     */
    public function success(Payment $payment): View
    {
        $this->resolveTenantAndValidate($payment);

        $payment->load('student.course');
        $setting = Setting::current();

        return view('payments.success', compact('payment', 'setting'));
    }

    /**
     * Descarga pública del recibo oficial en PDF para padres (protegido por token único).
     */
    public function publicReceipt(Payment $payment)
    {
        $this->resolveTenantAndValidate($payment);

        if ($payment->status !== 'pagado') {
            abort(403, 'El recibo oficial solo se encuentra disponible para colegiaturas liquidadas.');
        }

        $payment->load('student.course');
        $setting = Setting::current();

        $items = Payment::where('student_id', $payment->student_id)
            ->where('period', $payment->period)
            ->where('status', $payment->status)
            ->when($payment->paid_date, fn ($q) => $q->whereDate('paid_date', $payment->paid_date->toDateString()))
            ->get();

        if ($items->isEmpty() || ! $items->contains('id', $payment->id)) {
            $items = collect([$payment]);
        }

        $totalAmount = (float) $items->sum('amount');

        return Pdf::loadView('documents.receipt', compact('payment', 'items', 'totalAmount', 'setting'))
            ->setPaper('letter', 'portrait')
            ->download('Recibo_'.$payment->invoice_number.'.pdf');
    }

    /**
     * Subida de comprobante para transferencia SPEI.
     */
    public function uploadSpeiProof(Request $request, Payment $payment): RedirectResponse
    {
        $this->resolveTenantAndValidate($payment);

        $request->validate([
            'voucher' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $folder = "vouchers/school_{$payment->school_id}";
        $path = $request->file('voucher')->store($folder, 'public');

        $payment->update([
            'remarks' => 'Comprobante SPEI subido el ' . now()->format('d/m/Y H:i') . ' (' . $path . ')',
        ]);

        return redirect()->route('parent.payments.checkout', $payment)
            ->with('success', 'Comprobante de transferencia enviado correctamente. El área administrativa lo validará a la brevedad.');
    }

    /**
     * Aísla el tenant del colegio de este pago y valida su vigencia operativa.
     */
    private function resolveTenantAndValidate(Payment $payment): void
    {
        app(Tenancy::class)->set($payment->school_id);

        if (! $payment->school || ! $payment->school->isActive()) {
            abort(403, 'La institución educativa asociada a este pago no se encuentra disponible.');
        }
    }

    private function canAccessPayment($user, Payment $payment): bool
    {
        // Enlace público protegido por token criptográfico único (40 caracteres)
        if (! $user) {
            return true;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasAnyRole(['admin', 'secretaria'])) {
            return $user->school_id === $payment->school_id;
        }

        if ($user->hasRole('padre')) {
            return $payment->student->guardians()->where('users.id', $user->id)->exists();
        }

        if ($user->hasRole('estudiante')) {
            return $payment->student->user_id === $user->id;
        }

        return false;
    }
}
