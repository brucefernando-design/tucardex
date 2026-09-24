<?php

namespace Tests\Feature;

use App\Services\Facturacion\SunatBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BillingTestHelpers;
use Tests\TestCase;

class ResumenDiarioTest extends TestCase
{
    use BillingTestHelpers, RefreshDatabase;

    public function test_en_modo_resumen_las_boletas_quedan_pendientes_sin_enviar(): void
    {
        $this->bootTenant();
        $this->makeSettings(['boletas_por_resumen' => true]);
        $student = $this->makeStudent();

        $boleta = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '03');

        $this->assertSame('pendiente', $boleta->estado);
        // El driver no debe haber sido invocado para envío individual.
        $this->assertCount(0, $this->fakeDriver->emitted);
    }

    public function test_genera_resumen_y_consulta_ticket(): void
    {
        $this->bootTenant();
        $this->makeSettings(['boletas_por_resumen' => true]);
        $student = $this->makeStudent();

        $b1 = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '03');
        $b2 = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '03');

        $summary = app(SunatBilling::class)->generateDailySummary(now()->toDateString());

        $this->assertSame(2, $summary->total_docs);
        $this->assertSame('ticket', $summary->estado);
        $this->assertNotEmpty($summary->ticket);

        // Las boletas quedan vinculadas al resumen.
        $this->assertSame($summary->id, $b1->refresh()->summary_id);
        $this->assertSame($summary->id, $b2->refresh()->summary_id);

        // Consultar el ticket las marca como aceptadas.
        $summary = app(SunatBilling::class)->checkSummary($summary);

        $this->assertSame('aceptado', $summary->estado);
        $this->assertSame('aceptado', $b1->refresh()->estado);
        $this->assertSame('aceptado', $b2->refresh()->estado);
    }

    public function test_resumen_vacio_no_falla(): void
    {
        $this->bootTenant();
        $this->makeSettings(['boletas_por_resumen' => true]);

        $summary = app(SunatBilling::class)->generateDailySummary(now()->toDateString());

        $this->assertSame(0, $summary->total_docs);
        $this->assertSame('error', $summary->estado);
    }
}
