<?php

namespace App\Jobs;

use App\Mail\PaymentReminderMail;
use App\Models\Payment;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(
        public Payment $payment,
        public bool $sendWhatsApp = true,
        public bool $sendEmail = true
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $this->payment->refresh();

        // Si ya fue pagado en el intermedio, abortar de inmediato
        if (strtolower($this->payment->status) === 'pagado') {
            Log::info("SendPaymentReminderJob: Pago #{$this->payment->id} ya se encuentra pagado. Se omite recordatorio.");
            return;
        }

        $student = $this->payment->student;
        if (!$student) {
            return;
        }

        $sentChannels = [];

        // 1. Envío por WhatsApp
        if ($this->sendWhatsApp) {
            $phone = $student->guardian_phone ?: $student->phone;
            if ($phone) {
                $message = $whatsapp->buildReminderMessage($this->payment);
                $res = $whatsapp->sendMessage($phone, $message, $student->school_id);
                if ($res['ok']) {
                    $sentChannels[] = 'whatsapp';
                    Log::info("Recordatorio WhatsApp enviado exitosamente para Pago #{$this->payment->id} a {$phone}");
                } else {
                    Log::warning("Fallo WhatsApp para Pago #{$this->payment->id}: " . ($res['error'] ?? 'Error desconocido'));
                }
            }
        }

        // 2. Envío por Correo Electrónico
        if ($this->sendEmail) {
            $email = $student->guardian_email ?: $student->email;
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($email)->send(new PaymentReminderMail($this->payment));
                    $sentChannels[] = 'email';
                    Log::info("Recordatorio por Correo enviado exitosamente para Pago #{$this->payment->id} a {$email}");
                } catch (\Throwable $e) {
                    Log::error("Fallo al enviar correo para Pago #{$this->payment->id}: " . $e->getMessage());
                }
            }
        }

        if (!empty($sentChannels)) {
            $this->payment->update([
                'reminder_sent_at' => now(),
                'reminder_count' => ((int) $this->payment->reminder_count) + 1,
                'last_reminder_channel' => implode('+', $sentChannels),
            ]);
        }
    }
}
