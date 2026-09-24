<?php

namespace Tests\Feature;

use App\Models\ElectronicInvoice;
use App\Services\Facturacion\SunatBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BillingTestHelpers;
use Tests\TestCase;

class EmisionComprobanteTest extends TestCase
{
    use BillingTestHelpers, RefreshDatabase;

    public function test_calcula_igv_desde_el_total_del_pago(): void
    {
        $this->bootTenant();
        $this->makeSettings();
        $student = $this->makeStudent();
        $payment = $this->makePayment($student, ['amount' => 118.00]);

        $invoice = app(SunatBilling::class)->emitForPayment($payment, '03');

        // 118.00 con IGV 18% => base 100.00 + IGV 18.00
        $this->assertEquals(100.00, (float) $invoice->op_gravadas);
        $this->assertEquals(18.00, (float) $invoice->igv);
        $this->assertEquals(118.00, (float) $invoice->total);
        $this->assertSame('aceptado', $invoice->estado);
        $this->assertSame('FAKEHASH123', $invoice->hash);
    }

    public function test_la_numeracion_correlativa_es_secuencial_por_serie(): void
    {
        $this->bootTenant();
        $this->makeSettings();
        $student = $this->makeStudent();

        $a = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '03');
        $b = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '03');

        $this->assertSame('B001-00000001', $a->full_number);
        $this->assertSame('B001-00000002', $b->full_number);

        // Las facturas llevan su propia serie/numeración independiente.
        $f = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '01');
        $this->assertSame('F001-00000001', $f->full_number);
    }

    public function test_boleta_usa_dni_del_estudiante_y_factura_usa_ruc(): void
    {
        $this->bootTenant();
        $this->makeSettings();
        $student = $this->makeStudent(['dni' => '87654321']);

        $boleta = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '03');
        $this->assertSame('1', $boleta->client_tipo_doc); // DNI
        $this->assertSame('87654321', $boleta->client_num_doc);

        $factura = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '01');
        $this->assertSame('6', $factura->client_tipo_doc); // RUC
    }

    public function test_no_duplica_el_correlativo_al_reenviar(): void
    {
        $this->bootTenant();
        $this->makeSettings();
        $student = $this->makeStudent();
        $invoice = app(SunatBilling::class)->emitForPayment($this->makePayment($student), '03');

        app(SunatBilling::class)->send($invoice->refresh());

        $this->assertSame(1, ElectronicInvoice::where('serie', 'B001')->count());
    }
}
