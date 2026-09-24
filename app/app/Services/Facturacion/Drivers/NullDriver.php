<?php

namespace App\Services\Facturacion\Drivers;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\ElectronicSummary;
use App\Services\Facturacion\BillingResult;
use App\Services\Facturacion\Contracts\BillingDriver;

/**
 * Driver "Ninguno": no realiza ninguna emisión real ante SUNAT.
 * El comprobante queda registrado y en estado pendiente. Útil para
 * operar el sistema sin conexión a SUNAT o antes de instalar Greenter.
 */
class NullDriver implements BillingDriver
{
    public function testConnection(ElectronicBillingSetting $settings): BillingResult
    {
        return BillingResult::fail(
            'El driver de emisión es "Ninguno": no se envía nada a SUNAT. Selecciona un driver de emisión (Greenter) para emitir comprobantes.',
            'NO_DRIVER',
            'pendiente'
        );
    }

    public function emit(ElectronicInvoice $invoice, ElectronicBillingSetting $settings): BillingResult
    {
        return BillingResult::pending(
            'Comprobante '.$invoice->full_number.' generado localmente. Queda PENDIENTE porque el driver de emisión es "Ninguno".'
        );
    }

    public function voidDocument(ElectronicInvoice $invoice, ElectronicBillingSetting $settings): BillingResult
    {
        return BillingResult::ok('anulado', null, 'Comprobante anulado localmente (sin comunicación a SUNAT).');
    }

    public function sendSummary(ElectronicSummary $summary, array $invoices, ElectronicBillingSetting $settings): BillingResult
    {
        return BillingResult::fail(
            'Driver "Ninguno": el resumen diario no se envía a SUNAT. Selecciona el driver Greenter.',
            'NO_DRIVER',
            'pendiente'
        );
    }

    public function checkTicket(string $ticket, ElectronicBillingSetting $settings): BillingResult
    {
        return BillingResult::fail('Driver "Ninguno": no hay ticket que consultar.', 'NO_DRIVER', 'pendiente');
    }

    public function name(): string
    {
        return 'none';
    }
}
