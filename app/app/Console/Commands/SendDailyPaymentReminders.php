<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminderJob;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Console\Command;

class SendDailyPaymentReminders extends Command
{
    protected $signature = 'tucardex:reminders:dispatch {--force : Forzar ejecución ignorando hora programada} {--school= : ID de la escuela específica}';
    protected $description = 'Dispara la cobranza de colegiaturas con pausas aleatorias humanas (45s a 1m58s)';

    public function handle(): int
    {
        $settings = Setting::current();

        $this->info("=== TuKardex: Motor de Cobranza y Recordatorios ===");

        $daysBefore = (int) ($settings->reminder_days_before ?: 3);
        $daysAfter = (int) ($settings->reminder_days_after ?: 3);
        $minDelay = (int) ($settings->reminder_min_delay ?: 45);
        $maxDelay = (int) ($settings->reminder_max_delay ?: 118);

        $now = now();
        $targetBefore = $now->copy()->addDays($daysBefore)->format('Y-m-d');
        $targetToday = $now->format('Y-m-d');
        $targetAfter = $now->copy()->subDays($daysAfter)->format('Y-m-d');

        // Buscar pagos pendientes que ameriten recordatorio
        $query = Payment::where('status', '!=', 'pagado')
            ->where(function ($q) use ($targetBefore, $targetToday, $targetAfter) {
                $q->whereDate('due_date', $targetBefore) // 3 días antes
                  ->orWhereDate('due_date', $targetToday) // Mero día
                  ->orWhereDate('due_date', $targetAfter) // 3 días después
                  ->orWhere(function ($sub) {
                      // Vencidos en general que no hayan recibido recordatorio en los últimos 3 días
                      $sub->where('due_date', '<', now())
                          ->where(function ($rec) {
                              $rec->whereNull('reminder_sent_at')
                                  ->orWhere('reminder_sent_at', '<', now()->subDays(3));
                          });
                  });
            })
            ->with(['student.course', 'student']);

        if ($schoolId = $this->option('school')) {
            $query->whereHas('student', function ($s) use ($schoolId) {
                $s->where('school_id', $schoolId);
            });
        }

        $payments = $query->get();

        if ($payments->isEmpty()) {
            $this->info("No hay pagos pendientes que requieran recordatorio hoy.");
            return 0;
        }

        $this->info("Se encontraron {$payments->count()} pagos para notificar.");
        $this->info("Aplicando pausas aleatorias humanas (entre {$minDelay}s y {$maxDelay}s)...");

        $cumulativeDelay = 0;
        $count = 0;

        foreach ($payments as $payment) {
            $student = $payment->student;
            if (!$student) {
                continue;
            }

            // Pausa aleatoria no predecible (45s, 58s, 1m10s, 1m58s...)
            $randomJitter = rand($minDelay, $maxDelay);
            $cumulativeDelay += $randomJitter;
            $scheduledAt = now()->addSeconds($cumulativeDelay);

            SendPaymentReminderJob::dispatch(
                $payment,
                (bool) $settings->whatsapp_enabled,
                (bool) $settings->email_reminders_enabled
            )->delay($scheduledAt);

            $count++;
            $minutes = floor($cumulativeDelay / 60);
            $seconds = $cumulativeDelay % 60;
            $this->line(" [{$count}/{$payments->count()}] Pago #{$payment->id} ({$student->full_name}) -> Programado en +{$minutes}m {$seconds}s (Pausa: {$randomJitter}s)");
        }

        $totalMin = round($cumulativeDelay / 60, 1);
        $this->info("✅ {$count} recordatorios programados en segundo plano. Duración total del lote: {$totalMin} minutos.");
        return 0;
    }
}
