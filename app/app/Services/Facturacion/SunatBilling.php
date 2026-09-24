<?php

namespace App\Services\Facturacion;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\ElectronicSummary;
use App\Models\Payment;
use App\Services\Facturacion\Contracts\BillingDriver;
use App\Services\Facturacion\Drivers\GreenterDriver;
use App\Services\Facturacion\Drivers\NullDriver;

/**
 * Orquestador de la facturación electrónica: resuelve el driver activo,
 * arma el comprobante a partir de un Pago, lo persiste y delega la
 * emisión / prueba de conexión / anulación al driver correspondiente.
 */
class SunatBilling
{
    public function driverFor(ElectronicBillingSetting $settings): BillingDriver
    {
        // Se resuelven vía contenedor para poder sustituirlos en pruebas.
        return match ($settings->driver) {
            'greenter' => app(GreenterDriver::class),
            default => app(NullDriver::class),
        };
    }

    /** Prueba la conexión con SUNAT usando la configuración actual. */
    public function testConnection(?ElectronicBillingSetting $settings = null): BillingResult
    {
        $settings ??= ElectronicBillingSetting::current();

        return $this->driverFor($settings)->testConnection($settings);
    }

    /**
     * Emite (o registra) un comprobante a partir de un Pago del colegio.
     * $tipoDoc: '03' boleta (por defecto) | '01' factura.
     */
    public function emitForPayment(Payment $payment, string $tipoDoc = '03'): ElectronicInvoice
    {
        $settings = ElectronicBillingSetting::current();

        $invoice = $this->buildInvoiceFromPayment($payment, $settings, $tipoDoc);
        $invoice->save();

        return $this->send($invoice, $settings);
    }

    /** Envía a SUNAT un comprobante ya persistido y actualiza su estado. */
    public function send(ElectronicInvoice $invoice, ?ElectronicBillingSetting $settings = null): ElectronicInvoice
    {
        $settings ??= ElectronicBillingSetting::current();

        // En modo "boletas por resumen" las boletas no se envían individualmente:
        // quedan pendientes para incluirse en el Resumen Diario.
        if ($invoice->tipo_doc === '03' && $settings->boletas_por_resumen) {
            $invoice->update([
                'estado' => 'pendiente',
                'sunat_description' => 'Pendiente de Resumen Diario.',
            ]);

            return $invoice->refresh();
        }

        $invoice->update(['estado' => 'enviando']);

        $result = $this->driverFor($settings)->emit($invoice, $settings);

        $invoice->update([
            'estado' => $result->status,
            'sunat_code' => $result->code,
            'sunat_description' => $result->message,
            'hash' => $result->hash ?? $invoice->hash,
            'xml_path' => $result->xmlPath ?? $invoice->xml_path,
            'cdr_path' => $result->cdrPath ?? $invoice->cdr_path,
            'ticket' => $result->ticket ?? $invoice->ticket,
            'error_message' => $result->success ? null : $result->message,
        ]);

        return $invoice->refresh();
    }

    /**
     * Emite una Nota de crédito que referencia a un comprobante aceptado.
     * Si el motivo es anulación/devolución total, el comprobante original
     * queda marcado como anulado tras aceptarse la nota.
     */
    public function createCreditNote(ElectronicInvoice $original, string $codMotivo, ?string $motivo = null): ElectronicInvoice
    {
        $settings = ElectronicBillingSetting::current();

        $serie = $original->tipo_doc === '01'
            ? ($settings->serie_nc_factura ?: 'FC01')
            : ($settings->serie_nc_boleta ?: 'BC01');

        $correlativo = ElectronicInvoice::nextCorrelativo($settings->school_id, $serie);
        $motivoTexto = $motivo ?: (ElectronicInvoice::MOTIVOS_NC[$codMotivo] ?? 'Anulación de la operación');

        $note = new ElectronicInvoice([
            'school_id' => $settings->school_id,
            'payment_id' => $original->payment_id,
            'tipo_doc' => '07',
            'ref_tipo_doc' => $original->tipo_doc,
            'ref_full_number' => $original->full_number,
            'nota_cod_motivo' => $codMotivo,
            'nota_motivo' => $motivoTexto,
            'serie' => $serie,
            'correlativo' => $correlativo,
            'full_number' => $serie.'-'.str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT),
            'fecha_emision' => now(),
            'moneda' => $original->moneda,
            'client_tipo_doc' => $original->client_tipo_doc,
            'client_num_doc' => $original->client_num_doc,
            'client_razon_social' => $original->client_razon_social,
            'client_direccion' => $original->client_direccion,
            'op_gravadas' => $original->op_gravadas,
            'igv' => $original->igv,
            'total' => $original->total,
            'items' => $original->items,
            'estado' => 'pendiente',
        ]);
        $note->save();

        $note = $this->send($note, $settings);

        // Anular el original cuando la nota fue aceptada y el motivo lo amerita.
        if ($note->estado === 'aceptado' && in_array($codMotivo, ['01', '02', '06'], true)) {
            $original->update(['estado' => 'anulado']);
        }

        return $note;
    }

    /**
     * Genera y envía el Resumen Diario (RC) de las boletas pendientes de una
     * fecha. Devuelve el resumen con el ticket para consultar el CDR.
     */
    public function generateDailySummary($fecha, ?ElectronicBillingSetting $settings = null): ElectronicSummary
    {
        $settings ??= ElectronicBillingSetting::current();
        $fecha = $fecha instanceof \DateTimeInterface ? $fecha : new \DateTime($fecha);

        $boletas = ElectronicInvoice::where('tipo_doc', '03')
            ->whereNull('summary_id')
            ->where('estado', 'pendiente')
            ->whereDate('fecha_emision', $fecha->format('Y-m-d'))
            ->get();

        $correlativo = ElectronicSummary::nextCorrelativo($settings->school_id, $fecha->format('Y-m-d'));

        $summary = ElectronicSummary::create([
            'school_id' => $settings->school_id,
            'fecha_generacion' => $fecha->format('Y-m-d'),
            'fecha_resumen' => now()->format('Y-m-d'),
            'correlativo' => $correlativo,
            'identifier' => 'RC-'.$fecha->format('Ymd').'-'.$correlativo,
            'estado' => 'enviando',
            'total_docs' => $boletas->count(),
            'total' => (float) $boletas->sum('total'),
        ]);

        if ($boletas->isEmpty()) {
            $summary->update(['estado' => 'error', 'error_message' => 'No hay boletas pendientes para esa fecha.']);

            return $summary;
        }

        $result = $this->driverFor($settings)->sendSummary($summary, $boletas->all(), $settings);

        $summary->update([
            'estado' => $result->status,
            'ticket' => $result->ticket,
            'sunat_code' => $result->code,
            'sunat_description' => $result->message,
            'xml_path' => $result->xmlPath,
            'error_message' => $result->success ? null : $result->message,
        ]);

        if ($result->success && $result->ticket) {
            $boletas->each(fn ($b) => $b->update(['summary_id' => $summary->id]));
        }

        return $summary->refresh();
    }

    /** Consulta el estado del ticket de un resumen y propaga el resultado. */
    public function checkSummary(ElectronicSummary $summary, ?ElectronicBillingSetting $settings = null): ElectronicSummary
    {
        $settings ??= ElectronicBillingSetting::current();

        if (blank($summary->ticket)) {
            $summary->update(['error_message' => 'El resumen no tiene ticket asociado.']);

            return $summary;
        }

        $result = $this->driverFor($settings)->checkTicket($summary->ticket, $settings);

        $summary->update([
            'estado' => $result->status,
            'sunat_code' => $result->code,
            'sunat_description' => $result->message,
            'cdr_path' => $result->cdrPath ?? $summary->cdr_path,
            'error_message' => $result->success ? null : $result->message,
        ]);

        // Propagar el estado a las boletas del resumen.
        if (in_array($result->status, ['aceptado', 'observado'], true)) {
            $summary->invoices()->update([
                'estado' => $result->status,
                'sunat_code' => $result->code,
                'sunat_description' => 'Incluida en '.$summary->identifier,
            ]);
        }

        return $summary->refresh();
    }

    /** Anula un comprobante (baja / resumen de anulación). */
    public function void(ElectronicInvoice $invoice, ?ElectronicBillingSetting $settings = null): ElectronicInvoice
    {
        $settings ??= ElectronicBillingSetting::current();

        $result = $this->driverFor($settings)->voidDocument($invoice, $settings);

        $invoice->update([
            'estado' => $result->success ? 'anulado' : $invoice->estado,
            'sunat_description' => $result->message,
            'ticket' => $result->ticket ?? $invoice->ticket,
            'error_message' => $result->success ? null : $result->message,
        ]);

        return $invoice->refresh();
    }

    /**
     * Construye (sin guardar) el comprobante a partir de un Pago.
     * El tipo de cliente se deduce: si el estudiante tiene DNI se usa boleta
     * con DNI; para factura se requiere un RUC en el apoderado.
     */
    public function buildInvoiceFromPayment(Payment $payment, ElectronicBillingSetting $settings, string $tipoDoc = '03'): ElectronicInvoice
    {
        $payment->loadMissing('student');
        $student = $payment->student;

        $serie = $tipoDoc === '01' ? ($settings->serie_factura ?: 'F001') : ($settings->serie_boleta ?: 'B001');
        $correlativo = ElectronicInvoice::nextCorrelativo($settings->school_id, $serie);

        // Cliente
        if ($tipoDoc === '01') {
            $clientTipoDoc = '04'; // RFC México
            $clientNumDoc = $student->dni ?: '';
            $clientNombre = $student->guardian_name ?: ($student->full_name ?? 'CLIENTE');
        } elseif ($student && filled($student->dni)) {
            $clientTipoDoc = '05'; // CURP México
            $clientNumDoc = $student->curp ?? $student->dni ?? '';
            $clientNombre = $student->guardian_name ?: $student->full_name;
        } else {
            $clientTipoDoc = 'S01'; // Sin efectos fiscales (SAT México)
            $clientNumDoc = 'XAXX010101000'; // RFC Público en General
            $clientNombre = optional($student)->full_name ?: 'PÚBLICO EN GENERAL';
        }

        // México: Colegiaturas con RVOE son EXENTAS de IVA (Art. 15 Fracc. IV LIVA).
        // CFDI 4.0: ObjetoImp=02, TipoFactor=Exento, Impuesto=002, Importe=0.
        $total = round((float) $payment->amount, 2);
        $gravadas = 0.00;    // Sin base gravada (Exento, no es tasa 0)
        $igv = 0.00;         // IVA = $0
        $valorUnit = $total; // El monto total es el valor unitario del servicio

        $items = [[
            'codigo' => 'COLEGIATURA',
            'descripcion' => $payment->concept.($payment->period ? ' · '.$payment->period : ''),
            'unidad' => 'E48', // Unidad de servicio SAT México
            'cantidad' => 1,
            'valor_unit' => $valorUnit,
        ]];

        return new ElectronicInvoice([
            'school_id' => $settings->school_id,
            'payment_id' => $payment->id,
            'tipo_doc' => $tipoDoc,
            'serie' => $serie,
            'correlativo' => $correlativo,
            'full_number' => $serie.'-'.str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT),
            'fecha_emision' => now(),
            'moneda' => $settings->moneda ?: 'MXN',
            'client_tipo_doc' => $clientTipoDoc,
            'client_num_doc' => $clientNumDoc,
            'client_razon_social' => $clientNombre,
            'client_direccion' => optional($student)->address,
            'op_gravadas' => $gravadas,
            'igv' => $igv,
            'total' => $total,
            'items' => $items,
            'estado' => 'pendiente',
        ]);
    }
}
