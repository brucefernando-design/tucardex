<?php

namespace App\Console\Commands;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicSummary;
use App\Services\Facturacion\SunatBilling;
use App\Services\Tenancy;
use Illuminate\Console\Command;
use Throwable;

/**
 * Consulta en SUNAT el estado de los tickets de resúmenes diarios que aún
 * están pendientes de resolución, para todos los colegios. Al aceptarse,
 * las boletas del resumen quedan marcadas como aceptadas.
 *
 *   php artisan facturacion:consultar-tickets
 */
class ConsultarTicketsResumen extends Command
{
    protected $signature = 'facturacion:consultar-tickets';

    protected $description = 'Consulta los tickets de resúmenes diarios pendientes en SUNAT';

    public function handle(SunatBilling $billing, Tenancy $tenancy): int
    {
        // Colegios con emisión real activa.
        $schoolIds = ElectronicBillingSetting::query()
            ->where('enabled', true)
            ->where('driver', 'greenter')
            ->pluck('school_id')
            ->all();

        $pending = ElectronicSummary::query()
            ->whereNotNull('ticket')
            ->whereIn('estado', ['ticket', 'enviando', 'observado'])
            ->when($schoolIds, fn ($q) => $q->whereIn('school_id', $schoolIds))
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No hay tickets de resumen pendientes.');

            return self::SUCCESS;
        }

        $this->info("Consultando {$pending->count()} ticket(s)...");
        $resueltos = 0;

        foreach ($pending as $summary) {
            $tenancy->set($summary->school_id);
            try {
                $summary = $billing->checkSummary($summary);
                $this->line("  • {$summary->identifier} → {$summary->estado}".($summary->sunat_code ? " [{$summary->sunat_code}]" : ''));
                if ($summary->estado === 'aceptado') {
                    $resueltos++;
                }
            } catch (Throwable $e) {
                $this->error("  ✗ {$summary->identifier}: {$e->getMessage()}");
            } finally {
                $tenancy->forget();
            }
        }

        $this->info("Listo. Resúmenes aceptados en esta corrida: {$resueltos}.");

        return self::SUCCESS;
    }
}
