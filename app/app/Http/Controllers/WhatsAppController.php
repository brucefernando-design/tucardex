<?php

namespace App\Http\Controllers;

use App\Jobs\SendPaymentReminderJob;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsapp
    ) {}

    public function index(): View
    {
        $settings = Setting::current();
        $statusData = $this->whatsapp->getStatus();
        $qrData = ($statusData['status'] ?? '') !== 'connected' ? $this->whatsapp->getQrCode() : null;

        // Estadísticas de pagos pendientes
        $pendientesCount = Payment::where('status', '!=', 'pagado')->count();
        $vencidosCount = Payment::where('status', '!=', 'pagado')->where('due_date', '<', now())->count();
        $notificadosCount = Payment::whereNotNull('reminder_sent_at')->count();

        return view('configuracion.whatsapp', compact(
            'settings',
            'statusData',
            'qrData',
            'pendientesCount',
            'vencidosCount',
            'notificadosCount'
        ));
    }

    public function status(): JsonResponse
    {
        return response()->json($this->whatsapp->getStatus());
    }

    public function qr(): JsonResponse
    {
        return response()->json($this->whatsapp->getQrCode());
    }

    public function logout(): RedirectResponse
    {
        $this->whatsapp->logout();
        return back()->with('success', 'Sesión de WhatsApp cerrada exitosamente.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'email_reminders_enabled' => ['nullable', 'boolean'],
            'reminder_days_before' => ['required', 'integer', 'min:1', 'max:15'],
            'reminder_days_after' => ['required', 'integer', 'min:1', 'max:15'],
            'reminder_min_delay' => ['required', 'integer', 'min:15', 'max:300'],
            'reminder_max_delay' => ['required', 'integer', 'min:20', 'max:600'],
            'reminder_hour' => ['required', 'string', 'max:5'],
        ]);

        $validated['whatsapp_enabled'] = $request->boolean('whatsapp_enabled');
        $validated['email_reminders_enabled'] = $request->boolean('email_reminders_enabled');

        $settings = Setting::current();
        $settings->fill($validated)->save();

        return back()->with('success', 'Configuración de recordatorios de cobranza guardada.');
    }

    public function testSend(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'min:10'],
            'message' => ['required', 'string', 'max:500'],
        ]);

        $res = $this->whatsapp->sendMessage($request->input('phone'), $request->input('message'));

        if ($res['ok'] ?? false) {
            return back()->with('success', '✅ Mensaje de prueba enviado exitosamente por WhatsApp.');
        }

        return back()->with('error', '❌ Error al enviar mensaje: ' . ($res['error'] ?? 'WhatsApp no conectado.'));
    }

    public function dispararCobranza(): RedirectResponse
    {
        Artisan::call('tucardex:reminders:dispatch');
        $output = Artisan::output();

        return back()->with('success', '🚀 Proceso de cobranza disparado en segundo plano con pausas aleatorias humanas. ' . trim($output));
    }

    public function recordarPago(Payment $payment): RedirectResponse
    {
        $settings = Setting::current();

        SendPaymentReminderJob::dispatch(
            $payment,
            (bool) $settings->whatsapp_enabled,
            (bool) $settings->email_reminders_enabled
        );

        return back()->with('success', "Recordatorio encolado para el alumno {$payment->student->full_name}.");
    }
}
