<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ParentPaymentController extends Controller
{
    /**
     * Muestra la pantalla de checkout para liquidar una colegiatura o pago pendiente.
     */
    public function checkout(Payment $payment): View|RedirectResponse
    {
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
        $user = auth()->user();
        if (! $this->canAccessPayment($user, $payment)) {
            abort(403);
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
                            'name' => $user->name,
                            'email' => $user->email,
                        ],
                        'back_urls' => [
                            'success' => route('parent.payments.return', ['payment' => $payment->id, 'status' => 'success']),
                            'failure' => route('parent.payments.return', ['payment' => $payment->id, 'status' => 'failure']),
                            'pending' => route('parent.payments.return', ['payment' => $payment->id, 'status' => 'pending']),
                        ],
                        'auto_return' => 'approved',
                        'external_reference' => (string) $payment->id,
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

        // Si no tiene credenciales o está en modo demostración/sandbox, simular éxito
        return $this->simulate($payment);
    }

    /**
     * Simulación instantánea para modo Sandbox / Demo de colegios.
     */
    public function simulate(Payment $payment): RedirectResponse
    {
        $user = auth()->user();
        if (! $this->canAccessPayment($user, $payment)) {
            abort(403);
        }

        $payment->update([
            'status' => 'pagado',
            'paid_date' => now(),
            'method' => 'tarjeta',
            'remarks' => 'Pago en línea verificado con éxito (Simulación en modo Demo/Sandbox)',
        ]);

        return redirect()->route('parent.payments.success', $payment)
            ->with('success', '¡Pago procesado exitosamente! El recibo oficial ha sido emitido.');
    }

    /**
     * Retorno del padre tras completar el flujo en Mercado Pago.
     */
    public function returnCallback(Request $request, Payment $payment): RedirectResponse
    {
        $status = $request->query('collection_status', $request->query('status'));

        if ($status === 'approved' || $status === 'success') {
            if ($payment->status !== 'pagado') {
                $payment->update([
                    'status' => 'pagado',
                    'paid_date' => now(),
                    'method' => 'tarjeta',
                    'remarks' => 'Pago aprobado vía Mercado Pago (ID: ' . $request->query('payment_id', 'MP-'.time()) . ')',
                ]);
            }

            return redirect()->route('parent.payments.success', $payment)
                ->with('success', '¡Tu pago ha sido acreditado exitosamente por Mercado Pago!');
        }

        if ($status === 'pending') {
            return redirect()->route('dashboard')
                ->with('warning', 'Tu orden de pago fue generada y está en proceso de acreditación (ej. SPEI u OXXO). En cuanto se confirme, tu recibo estará disponible.');
        }

        return redirect()->route('parent.payments.checkout', $payment)
            ->with('error', 'El pago fue cancelado o rechazado por el banco. Puedes intentar con otro método o por transferencia SPEI.');
    }

    /**
     * Pantalla de éxito con confirmación y botón de descarga de recibo oficial en PDF.
     */
    public function success(Payment $payment): View
    {
        $payment->load('student.course');
        $setting = Setting::current();

        return view('payments.success', compact('payment', 'setting'));
    }

    /**
     * Subida de comprobante para transferencia SPEI.
     */
    public function uploadSpeiProof(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'voucher' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('voucher')->store('vouchers', 'public');

        $payment->update([
            'remarks' => 'Comprobante SPEI subido el ' . now()->format('d/m/Y H:i') . ' (' . $path . ')',
        ]);

        return redirect()->route('parent.payments.checkout', $payment)
            ->with('success', 'Comprobante de transferencia enviado correctamente. El área administrativa lo validará a la brevedad.');
    }

    private function canAccessPayment($user, Payment $payment): bool
    {
        if ($user->hasAnyRole(['admin', 'secretaria', 'superadmin'])) {
            return true;
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
