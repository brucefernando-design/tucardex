<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::with('student', 'electronicInvoices')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->whereHas('student', function ($sub) use ($s) {
                $sub->where('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $billing = \App\Models\ElectronicBillingSetting::current();

        $summary = [
            'paid' => (float) Payment::where('status', 'pagado')->sum('amount'),
            'pending' => (float) Payment::where('status', 'pendiente')->sum('amount'),
            'overdue' => (float) Payment::where('status', 'vencido')->sum('amount'),
        ];

        $students = Student::where('status', 'activo')->orderBy('last_name')->get();
        $courses = Course::orderBy('name')->get();

        return view('payments.index', compact('payments', 'summary', 'students', 'courses', 'billing'));
    }

    /**
     * Genera pensiones (pagos pendientes) para todos los estudiantes activos de un curso.
     */
    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'concept' => ['required', 'string', 'max:120'],
            'period' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
        ]);

        $students = Student::where('course_id', $data['course_id'])->where('status', 'activo')->get();
        $created = 0;
        $next = (int) Payment::max('id') + 1;
        $setting = Setting::current();

        foreach ($students as $student) {
            // Evitar duplicados: mismo concepto + período para el estudiante
            $exists = Payment::where('student_id', $student->id)
                ->where('concept', $data['concept'])
                ->where('period', $data['period'])
                ->exists();
            if ($exists) {
                continue;
            }

            Payment::create([
                'student_id' => $student->id,
                'invoice_number' => 'FAC-'.now()->format('Ymd').'-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT),
                'concept' => $data['concept'],
                'amount' => $data['amount'],
                'period' => $data['period'],
                'due_date' => $data['due_date'],
                'status' => 'pendiente',
            ]);

            Notification::notify(
                $student->user_id,
                'Nuevo cargo registrado',
                'payment',
                "{$data['concept']} · vence {$data['due_date']}",
                route('dashboard')
            );
            $created++;
            // Segundo cargo independiente si la plataforma digital está habilitada
            if ($setting->platform_fee_enabled && (float)$setting->platform_fee_amount > 0) {
                $feeLabel = $setting->platform_fee_label ?: 'Plataforma digital / portal familias';
                $feeExists = Payment::where('student_id', $student->id)
                    ->where('concept', $feeLabel)
                    ->where('period', $data['period'])
                    ->exists();

                if (! $feeExists) {
                    Payment::create([
                        'student_id' => $student->id,
                        'invoice_number' => 'FAC-'.now()->format('Ymd').'-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT),
                        'concept' => $feeLabel,
                        'amount' => $setting->platform_fee_amount,
                        'period' => $data['period'],
                        'due_date' => $data['due_date'],
                        'status' => 'pendiente',
                    ]);
                    $created++;
                }
            }
        }

        return redirect()->route('payments.index')->with('success', "Se generaron $created colegiaturas para el grupo seleccionado.");
    }

    /**
     * Recibo individual de un pago en PDF.
     */
    public function receipt(Payment $payment)
    {
        $user = auth()->user();
        if ($user && ! $user->hasAnyRole(['admin', 'secretaria', 'superadmin'])) {
            if ($user->hasRole('padre')) {
                $isGuardian = $payment->student->guardians()->where('users.id', $user->id)->exists();
                if (! $isGuardian) {
                    abort(403, 'No tienes autorización para ver este recibo.');
                }
            } elseif ($user->hasRole('estudiante')) {
                if ($payment->student->user_id !== $user->id) {
                    abort(403, 'No tienes autorización para ver este recibo.');
                }
            } else {
                abort(403);
            }
        }

        $payment->load('student.course');
        $setting = Setting::current();

        // Obtener cargos desglosados asociados al mismo alumno, período y fecha de pago (ej. Colegiatura + Plataforma Digital)
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
     * Reporte de morosos: estudiantes con saldo pendiente o vencido.
     */
    public function defaulters(Request $request)
    {
        $rows = Student::query()
            ->where('status', 'activo')->with(['course', 'payments'])
            ->whereHas('payments', fn ($q) => $q->whereIn('status', ['pendiente', 'vencido']))
            ->get()
            ->map(function ($s) {
                $debt = $s->payments->whereIn('status', ['pendiente', 'vencido']);

                return ['student' => $s, 'count' => $debt->count(), 'total' => (float) $debt->sum('amount')];
            })
            ->sortByDesc('total')->values();

        $setting = Setting::current();
        $grandTotal = $rows->sum('total');

        if ($request->get('format') === 'pdf') {
            return Pdf::loadView('exports.defaulters_pdf', compact('rows', 'setting', 'grandTotal'))
                ->setPaper('letter', 'portrait')
                ->download('Morosos_'.now()->format('Ymd').'.pdf');
        }

        return view('payments.defaulters', compact('rows', 'grandTotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'concept' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0'],
            'period' => ['nullable', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pendiente,pagado,vencido,anulado'],
            'method' => ['nullable', 'in:efectivo,transferencia,tarjeta,qr'],
        ]);

        $data['invoice_number'] = 'FAC-'.now()->format('Ymd').'-'.str_pad((string) (Payment::max('id') + 1), 4, '0', STR_PAD_LEFT);
        if ($data['status'] === 'pagado') {
            $data['paid_date'] = now();
        }

        Payment::create($data);

        if ($data['status'] !== 'pagado') {
            $student = Student::find($data['student_id']);
            Notification::notify(
                optional($student)->user_id,
                'Nuevo cargo registrado',
                'payment',
                "{$data['concept']} · ".($data['status']),
                route('dashboard')
            );
        }

        return redirect()->route('payments.index')->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Exporta el listado de pagos (respeta filtro de estado) a CSV o PDF.
     */
    public function export(Request $request)
    {
        $payments = Payment::with('student')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->get();

        $summary = [
            'paid' => (float) Payment::where('status', 'pagado')->sum('amount'),
            'pending' => (float) Payment::where('status', 'pendiente')->sum('amount'),
            'overdue' => (float) Payment::where('status', 'vencido')->sum('amount'),
        ];

        $setting = \App\Models\Setting::current();

        if ($request->get('format') === 'pdf') {
            return Pdf::loadView('exports.payments_pdf', compact('payments', 'summary', 'setting'))
                ->setPaper('letter', 'portrait')
                ->download('pagos_'.now()->format('Ymd').'.pdf');
        }

        return response()->streamDownload(function () use ($payments, $setting) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Factura', 'Estudiante', 'Concepto', 'Monto ('.$setting->currency.')', 'Periodo', 'Vence', 'Pagado', 'Método', 'Estado']);
            foreach ($payments as $p) {
                fputcsv($out, [
                    $p->invoice_number, optional($p->student)->full_name, $p->concept,
                    number_format($p->amount, 2, '.', ''), $p->period,
                    optional($p->due_date)->format('d/m/Y'), optional($p->paid_date)->format('d/m/Y'),
                    $p->method, $p->status,
                ]);
            }
            fclose($out);
        }, 'pagos_'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function markPaid(Payment $payment): RedirectResponse
    {
        $payment->update([
            'status' => 'pagado',
            'paid_date' => now(),
            'method' => $payment->method ?: 'efectivo',
        ]);

        // Emisión automática del comprobante electrónico si está configurada.
        $billing = \App\Models\ElectronicBillingSetting::current();
        if ($billing->enabled && $billing->auto_emit && ! $payment->comprobante()) {
            $invoice = app(\App\Services\Facturacion\FacturapiService::class)->emitForPayment($payment, '03');

            return redirect()->route('payments.index')->with(
                $invoice->estado === 'aceptado' || $invoice->estado === 'pendiente' ? 'success' : 'error',
                'Pago registrado. Comprobante '.$invoice->full_number.': '.$invoice->estado
                .($invoice->sunat_description ? ' — '.$invoice->sunat_description : '')
            );
        }

        return redirect()->route('payments.index')->with('success', 'Pago marcado como pagado.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Pago eliminado.');
    }

    public function gateways(): View
    {
        $settings = Setting::current();
        return view('payments.gateways', compact('settings'));
    }

    public function saveGateways(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'spei_enabled' => ['nullable', 'boolean'],
            'spei_bank' => ['nullable', 'string', 'max:60'],
            'spei_clabe' => ['nullable', 'string', 'max:18'],
            'spei_beneficiary' => ['nullable', 'string', 'max:150'],
            'spei_instructions' => ['nullable', 'string'],
            'mercadopago_enabled' => ['nullable', 'boolean'],
            'mercadopago_public_key' => ['nullable', 'string', 'max:255'],
            'mercadopago_access_token' => ['nullable', 'string', 'max:255'],
            'stripe_enabled' => ['nullable', 'boolean'],
            'stripe_public_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        $data['spei_enabled'] = $request->boolean('spei_enabled');
        $data['mercadopago_enabled'] = $request->boolean('mercadopago_enabled');
        $data['stripe_enabled'] = $request->boolean('stripe_enabled');

        $settings = Setting::current();
        $settings->fill($data)->save();

        return redirect()->route('payments.gateways')->with('success', 'Configuración de pasarelas y datos bancarios actualizada correctamente.');
    }
}
