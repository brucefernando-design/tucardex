<?php

namespace App\Services\Facturacion\Contracts;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\ElectronicSummary;
use App\Services\Facturacion\BillingResult;

/**
 * Contrato que debe cumplir cualquier motor de emisión electrónica
 * (Ninguno, Greenter, u otro proveedor).
 */
interface BillingDriver
{
    /** Verifica credenciales/certificado y conectividad con SUNAT. */
    public function testConnection(ElectronicBillingSetting $settings): BillingResult;

    /** Genera, firma y envía el comprobante a SUNAT. */
    public function emit(ElectronicInvoice $invoice, ElectronicBillingSetting $settings): BillingResult;

    /** Comunica la baja / anulación de un comprobante ya aceptado. */
    public function voidDocument(ElectronicInvoice $invoice, ElectronicBillingSetting $settings): BillingResult;

    /** Envía un Resumen Diario de boletas; devuelve el ticket en $result->ticket. */
    public function sendSummary(ElectronicSummary $summary, array $invoices, ElectronicBillingSetting $settings): BillingResult;

    /** Consulta el estado de un ticket (resumen / baja). */
    public function checkTicket(string $ticket, ElectronicBillingSetting $settings): BillingResult;

    /** Identificador corto del driver. */
    public function name(): string;
}
