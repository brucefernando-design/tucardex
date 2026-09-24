<?php

namespace Tests\Support;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\ElectronicSummary;
use App\Services\Facturacion\BillingResult;
use App\Services\Facturacion\Contracts\BillingDriver;

/**
 * Driver de facturación simulado para pruebas: no contacta a SUNAT, registra
 * las llamadas y devuelve resultados configurables.
 */
class FakeSunatDriver implements BillingDriver
{
    /** @var ElectronicInvoice[] */
    public array $emitted = [];

    /** @var ElectronicSummary[] */
    public array $summaries = [];

    public array $checkedTickets = [];

    public string $emitStatus = 'aceptado';

    public function name(): string
    {
        return 'fake';
    }

    public function testConnection(ElectronicBillingSetting $settings): BillingResult
    {
        return BillingResult::ok('aceptado', '0', 'Conexión simulada correcta.');
    }

    public function emit(ElectronicInvoice $invoice, ElectronicBillingSetting $settings): BillingResult
    {
        $this->emitted[] = $invoice;

        if ($this->emitStatus !== 'aceptado') {
            return BillingResult::fail('Rechazo simulado', '2027', $this->emitStatus);
        }

        return BillingResult::ok('aceptado', '0', 'Comprobante aceptado (simulado).', [
            'hash' => 'FAKEHASH123',
            'xmlPath' => 'facturacion/test/'.$invoice->full_number.'.xml',
            'cdrPath' => 'facturacion/test/'.$invoice->full_number.'.cdr.zip',
        ]);
    }

    public function voidDocument(ElectronicInvoice $invoice, ElectronicBillingSetting $settings): BillingResult
    {
        return BillingResult::ok('anulado', '0', 'Anulación simulada.');
    }

    public function sendSummary(ElectronicSummary $summary, array $invoices, ElectronicBillingSetting $settings): BillingResult
    {
        $this->summaries[] = $summary;

        return BillingResult::ok('ticket', null, 'Resumen aceptado (simulado).', [
            'ticket' => 'TCK-'.$summary->identifier,
            'xmlPath' => 'facturacion/test/'.$summary->identifier.'.xml',
        ]);
    }

    public function checkTicket(string $ticket, ElectronicBillingSetting $settings): BillingResult
    {
        $this->checkedTickets[] = $ticket;

        return new BillingResult(
            true, 'aceptado', '0', 'La Comunicacion de baja ha sido aceptada (simulado).',
            null, null, 'facturacion/test/R-'.$ticket.'.zip', $ticket, []
        );
    }
}
