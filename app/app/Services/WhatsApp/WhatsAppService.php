<?php

namespace App\Services\WhatsApp;

use App\Models\Payment;
use App\Models\Setting;
use App\Services\Tenancy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.whatsapp.url', 'http://tucardex-wa:3000'), '/');
    }

    protected function getSchoolId(?int $schoolId = null): int
    {
        if ($schoolId) {
            return $schoolId;
        }

        try {
            $tenancy = app(Tenancy::class);
            return (int) ($tenancy->id() ?: 1);
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Obtiene el estado actual de la conexión de WhatsApp de la escuela.
     */
    public function getStatus(?int $schoolId = null): array
    {
        $id = $this->getSchoolId($schoolId);

        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/status/{$id}");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning("WhatsAppService::getStatus error: " . $e->getMessage());
        }

        return [
            'ok' => false,
            'status' => 'disconnected',
            'phone' => null,
            'error' => 'No fue posible comunicar con el microservicio de WhatsApp',
        ];
    }

    /**
     * Obtiene el código QR en Base64 para mostrar en pantalla.
     */
    public function getQrCode(?int $schoolId = null): array
    {
        $id = $this->getSchoolId($schoolId);

        try {
            $response = Http::timeout(6)->get("{$this->baseUrl}/qr/{$id}");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::error("WhatsAppService::getQrCode error: " . $e->getMessage());
        }

        return [
            'ok' => false,
            'status' => 'disconnected',
            'qr' => null,
            'error' => 'Error al obtener código QR',
        ];
    }

    /**
     * Envía un mensaje de WhatsApp a un teléfono.
     */
    public function sendMessage(string $phone, string $message, ?int $schoolId = null): array
    {
        $id = $this->getSchoolId($schoolId);

        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/send/{$id}", [
                'phone' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'ok' => false,
                'error' => $response->json('error') ?? 'Error al enviar mensaje por WhatsApp',
            ];
        } catch (\Throwable $e) {
            Log::error("WhatsAppService::sendMessage error: " . $e->getMessage());
            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cierra la sesión de WhatsApp del colegio.
     */
    public function logout(?int $schoolId = null): array
    {
        $id = $this->getSchoolId($schoolId);

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/logout/{$id}");
            return $response->json();
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Construye un mensaje de cobranza amigable y personalizado con link de checkout.
     */
    public function buildReminderMessage(Payment $payment): string
    {
        $payment->loadMissing(['student.course', 'student']);
        $student = $payment->student;
        $course = optional($student)->course;
        $appSettings = Setting::current();

        $colegioNombre = $appSettings->school_name ?: 'Colegio';
        $tutorNombre = $student->guardian_name ?: ($student->first_name ? "Familia {$student->last_name}" : 'Estimado Padre de Familia');
        $alumnoNombre = $student->full_name ?? 'su hijo(a)';
        $gradoGrupo = $course ? " ({$course->name})" : '';
        $concepto = $payment->concept ?: 'Colegiatura';
        if ($payment->period) {
            $concepto .= " · {$payment->period}";
        }
        $montoFormateado = '$' . number_format((float) $payment->amount, 2) . ' ' . ($payment->currency ?: 'MXN');
        $fechaVencimiento = $payment->due_date ? $payment->due_date->format('d/m/Y') : 'próximamente';

        $checkoutUrl = route('parent.payments.checkout', $payment);

        // Saludos dinámicos para evitar mensajes idénticos (Spintax humano)
        $saludos = [
            "Hola, estimado(a) *{$tutorNombre}* 👋",
            "Buen día, estimado(a) *{$tutorNombre}* ☀️",
            "Estimado(a) *{$tutorNombre}*, le saludamos cordialmente 👋",
        ];
        $saludo = $saludos[$payment->id % count($saludos)];

        return "{$saludo}\n\n" .
            "Le contactamos de *{$colegioNombre}* para recordarle que la colegiatura de *{$alumnoNombre}*{$gradoGrupo} correspondiente a *{$concepto}* por un total de *{$montoFormateado}* vence el *{$fechaVencimiento}*.\n\n" .
            "💳 *Realice su pago en línea de forma segura y sin filas aquí:*\n" .
            "👉 {$checkoutUrl}\n\n" .
            "_Acepta transferencia SPEI, pago en efectivo en OXXO y tarjetas bancarias. Al pagar, su comprobante y factura SAT (Complemento IEDU) se generan en automático._\n\n" .
            "¡Agradecemos mucho su puntualidad y apoyo a la educación de {$alumnoNombre}!";
    }

    /**
     * Construye el mensaje amigable de aviso de inasistencia matutina con Spintax y datos del colegio.
     */
    public function buildAttendanceAlertMessage(\App\Models\Attendance $attendance): string
    {
        $attendance->loadMissing(['student.course', 'student', 'course']);
        $student = $attendance->student;
        $course = $attendance->course ?? optional($student)->course;
        $appSettings = \App\Models\Setting::current();

        $colegioNombre = $appSettings->school_name ?: 'Colegio';
        $tutorNombre = $student->guardian_name ?: ($student->first_name ? "Familia {$student->last_name}" : 'Estimado Padre de Familia');
        $alumnoNombre = $student->full_name ?? 'su hijo(a)';
        $gradoGrupo = $course ? " ({$course->name}" . ($course->section ? " "{$course->section}"" : '') . ")" : '';

        $fechaTexto = $attendance->date ? $attendance->date->locale('es')->isoFormat('dddd D [de] MMMM') : now()->locale('es')->isoFormat('dddd D [de] MMMM');
        $fechaTexto = ucfirst($fechaTexto);

        // Saludos dinámicos para evitar mensajes idénticos (Spintax humano)
        $saludos = [
            "Hola, estimado(a) *{$tutorNombre}* 👋",
            "Buen día, estimado(a) *{$tutorNombre}* ☀️",
            "Estimado(a) *{$tutorNombre}*, le saludamos cordialmente 👋",
        ];
        $saludo = $saludos[$student->id % count($saludos)];

        return "{$saludo}

" .
            "Le contactamos de *{$colegioNombre}* para informarle que el día de hoy, *{$fechaTexto}*, se registró la *inasistencia* de su hijo(a) *{$alumnoNombre}*{$gradoGrupo} en el pase de lista matutino.

" .
            "📌 *Importante:*
" .
            "Si la inasistencia se debe a algún motivo de salud o permiso familiar, le solicitamos comunicarse con la Dirección / Control Escolar para registrar el justificante correspondiente.

" .
            "¡Agradecemos mucho su atención y compromiso con la seguridad y formación de {$alumnoNombre}! 🎓";
    }
}
