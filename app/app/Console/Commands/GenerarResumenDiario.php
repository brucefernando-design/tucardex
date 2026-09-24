<?php

namespace App\Console\Commands;

use App\Models\ElectronicBillingSetting;
use App\Services\Facturacion\SunatBilling;
use App\Services\Tenancy;
use Illuminate\Console\Command;
use Throwable;

/**
 * Genera y envía a SUNAT el Resumen Diario de boletas de una fecha
 * (por defecto, el día anterior) para todos los colegios que tengan
 * activada la emisión con "boletas por resumen".
 *
 *   php artisan facturacion:resumen-diario
 *   php artisan facturacion:resumen-diario 2026-08-09
 */
class GenerarResumenDiario extends Command
{
    protected $signature = 'facturacion:resumen-diario {fecha? : Fecha de emisión de las boletas (Y-m-d). Por defecto: ayer}';

    protected $description = 'Genera y envía el Resumen Diario de boletas a SUNAT por cada colegio';

    public function handle(SunatBilling $billing, Tenancy $tenancy): int
    {
        $fecha = $this->argument('fecha') ?: now()->subDay()->toDateString();
        $this->info("Generando resúmenes diarios para la fecha {$fecha}...");

        $settings = ElectronicBillingSetting::query()
            ->where('enabled', true)
            ->where('driver', 'greenter')
            ->where('boletas_por_resumen', true)
            ->get();

        if ($settings->isEmpty()) {
            $this->warn('Ningún colegio tiene activado el modo "boletas por resumen". Nada que hacer.');

            return self::SUCCESS;
        }

        $ok = 0;
        $fail = 0;

        foreach ($settings as $s) {
            $tenancy->set($s->school_id);
            try {
                $summary = $billing->generateDailySummary($fecha, $s);

                if (in_array($summary->estado, ['ticket', 'aceptado'], true)) {
                    $ok++;
                    $this->line("  ✓ Colegio #{$s->school_id}: {$summary->identifier} ({$summary->total_docs} boletas) → {$summary->estado}".($summary->ticket ? " · ticket {$summary->ticket}" : ''));
                } else {
                    $fail++;
                    $this->line("  • Colegio #{$s->school_id}: {$summary->identifier} → {$summary->estado} — {$summary->error_message}");
                }
            } catch (Throwable $e) {
                $fail++;
                $this->error("  ✗ Colegio #{$s->school_id}: {$e->getMessage()}");
            } finally {
                $tenancy->forget();
            }
        }

        $this->info("Listo. Resúmenes enviados: {$ok} · con incidencias: {$fail}.");

        return self::SUCCESS;
    }
}
