<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAttendanceAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(
        public Attendance $attendance
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $this->attendance->refresh();

        // Si ya fue notificado previamente, cancelar de inmediato para no duplicar
        if ($this->attendance->notified_at !== null) {
            Log::info("SendAttendanceAlertJob: Asistencia #{$this->attendance->id} ya fue notificada previamente. Se omite.");
            return;
        }

        // Si el estado ya no es ausente (ej: el maestro corrigió a presente o justificado), no enviar
        if (!in_array(strtolower($this->attendance->status), ['ausente', 'falta'])) {
            Log::info("SendAttendanceAlertJob: Asistencia #{$this->attendance->id} ya no tiene estado ausente ({$this->attendance->status}). Se omite.");
            return;
        }

        $student = $this->attendance->student;
        if (!$student) {
            return;
        }

        $phone = $student->guardian_phone ?: $student->phone;
        if (!$phone) {
            Log::info("SendAttendanceAlertJob: Alumno #{$student->id} ({$student->full_name}) no tiene teléfono de contacto registrado.");
            return;
        }

        $message = $whatsapp->buildAttendanceAlertMessage($this->attendance);
        $res = $whatsapp->sendMessage($phone, $message, $student->school_id);

        if ($res['ok']) {
            $this->attendance->update([
                'notified_at' => now(),
            ]);
            Log::info("Alerta de inasistencia por WhatsApp enviada exitosamente para Alumno #{$student->id} ({$student->full_name}) a {$phone}");
        } else {
            Log::warning("Fallo al enviar alerta de inasistencia para Alumno #{$student->id}: " . ($res['error'] ?? 'Error desconocido'));
        }
    }
}
