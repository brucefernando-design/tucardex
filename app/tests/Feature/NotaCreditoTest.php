<?php

namespace Tests\Feature;

use App\Services\Facturacion\SunatBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BillingTestHelpers;
use Tests\TestCase;

class NotaCreditoTest extends TestCase
{
    use BillingTestHelpers, RefreshDatabase;

    public function test_emite_nota_de_credito_y_anula_el_original(): void
    {
        $this->bootTenant();
        $this->makeSettings();
        $student = $this->makeStudent();
        $boleta = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '03');
        $this->assertSame('aceptado', $boleta->estado);

        // Motivo 01 = anulación de la operación.
        $nota = app(SunatBilling::class)->createCreditNote($boleta, '01');

        $this->assertSame('07', $nota->tipo_doc);
        $this->assertSame('BC01-00000001', $nota->full_number);
        $this->assertSame($boleta->full_number, $nota->ref_full_number);
        $this->assertSame('03', $nota->ref_tipo_doc);
        $this->assertSame('aceptado', $nota->estado);

        // El comprobante original queda anulado.
        $this->assertSame('anulado', $boleta->refresh()->estado);
    }

    public function test_nota_de_credito_por_correccion_no_anula_el_original(): void
    {
        $this->bootTenant();
        $this->makeSettings();
        $student = $this->makeStudent();
        $factura = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '01');

        // Motivo 03 = corrección por error en la descripción (no anula).
        $nota = app(SunatBilling::class)->createCreditNote($factura, '03');

        $this->assertSame('FC01-00000001', $nota->full_number);
        $this->assertSame('aceptado', $factura->refresh()->estado);
    }
}
