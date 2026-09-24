<?php

namespace App\Services\Facturacion\Support;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use Throwable;

/**
 * Genera el código QR de la representación impresa de un comprobante,
 * con el contenido exigido por SUNAT:
 *
 *   RUC | TIPO_DOC | SERIE | CORRELATIVO | IGV | TOTAL | FECHA | TIPO_DOC_ADQ | NUM_DOC_ADQ | HASH
 *
 * Devuelve un Data URI PNG listo para incrustar en el PDF (dompdf).
 * Requiere la librería endroid/qr-code; si no está instalada devuelve null
 * y la plantilla muestra el contenido en texto.
 */
class QrGenerator
{
    public static function content(ElectronicInvoice $invoice, ElectronicBillingSetting $settings): string
    {
        return implode('|', [
            $settings->ruc,
            $invoice->tipo_doc,
            $invoice->serie,
            $invoice->correlativo,
            number_format((float) $invoice->igv, 2, '.', ''),
            number_format((float) $invoice->total, 2, '.', ''),
            optional($invoice->fecha_emision)->format('Y-m-d'),
            $invoice->client_tipo_doc,
            $invoice->client_num_doc,
            $invoice->hash,
        ]);
    }

    /** Data URI PNG del QR, o null si no hay librería disponible. */
    public static function dataUri(ElectronicInvoice $invoice, ElectronicBillingSetting $settings, int $size = 150): ?string
    {
        if (! class_exists(\Endroid\QrCode\QrCode::class) || ! class_exists(\Endroid\QrCode\Writer\PngWriter::class)) {
            return null;
        }

        try {
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $qr = \Endroid\QrCode\QrCode::create(static::content($invoice, $settings))
                ->setSize($size)
                ->setMargin(6);

            return $writer->write($qr)->getDataUri();
        } catch (Throwable $e) {
            return null;
        }
    }
}
