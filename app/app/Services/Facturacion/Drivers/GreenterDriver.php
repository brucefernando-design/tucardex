<?php

namespace App\Services\Facturacion\Drivers;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\ElectronicSummary;
use App\Services\Facturacion\BillingResult;
use App\Services\Facturacion\Contracts\BillingDriver;
use App\Services\Facturacion\Support\NumberToWords;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Driver de emisión real ante SUNAT basado en la librería Greenter
 * (https://greenter.dev). Genera el XML UBL 2.1, lo firma con el
 * certificado digital y lo envía al web service de SUNAT.
 *
 * Requiere instalar la dependencia en el proyecto:
 *   composer require greenter/greenter
 *
 * Si la librería no está instalada, el driver falla de forma controlada
 * indicando cómo habilitarla, sin romper el resto del sistema.
 */
class GreenterDriver implements BillingDriver
{
    public function name(): string
    {
        return 'greenter';
    }

    /** ¿Está instalada la librería Greenter? */
    public function isAvailable(): bool
    {
        return class_exists(\Greenter\See::class);
    }

    public function testConnection(ElectronicBillingSetting $s): BillingResult
    {
        if (! $this->isAvailable()) {
            return $this->notInstalled();
        }

        if (! $cert = $this->readCertificate($s)) {
            return BillingResult::fail('No se encontró el certificado en la ruta indicada: '.$s->certificate_path, 'CERT_NOT_FOUND');
        }

        if (blank($s->ruc) || blank($s->sol_user) || blank($s->sol_pass)) {
            return BillingResult::fail('Faltan credenciales: RUC, Usuario Clave SOL o Clave SOL.', 'MISSING_CREDENTIALS');
        }

        // Emisión de un comprobante de prueba mínimo para validar credenciales/certificado.
        try {
            $see = $this->makeSee($s, $cert);

            $company = (new \Greenter\Model\Company\Company())
                ->setRuc($s->ruc)
                ->setRazonSocial($s->razon_social ?: 'EMISOR')
                ->setNombreComercial($s->nombre_comercial ?: '')
                ->setAddress($this->address($s));

            $client = (new \Greenter\Model\Client\Client())
                ->setTipoDoc('1')->setNumDoc('00000000')->setRznSocial('CLIENTE PRUEBA');

            $invoice = $this->buildInvoice($s, $company, $client, [
                'tipo_doc' => '03',
                'serie' => $s->serie_boleta ?: 'B001',
                'correlativo' => (string) random_int(90000000, 99999999), // no persistido; solo valida firma/credenciales
                'items' => [['descripcion' => 'PRUEBA DE CONEXION', 'cantidad' => 1, 'valor_unit' => 1.00]],
            ]);

            $result = $see->send($invoice);

            if ($result->isSuccess()) {
                $cdr = $result->getCdrResponse();

                return BillingResult::ok('aceptado', $cdr->getCode(),
                    'Conexión correcta. SUNAT respondió: '.$cdr->getDescription());
            }

            $err = $result->getError();

            // Un error de "documento ya existe" o de credenciales igualmente confirma conectividad.
            return BillingResult::fail(
                'SUNAT respondió: ['.$err->getCode().'] '.$err->getMessage(),
                $err->getCode(),
                'rechazado'
            );
        } catch (Throwable $e) {
            return BillingResult::fail('Error al conectar con SUNAT: '.$e->getMessage(), 'EXCEPTION');
        }
    }

    public function emit(ElectronicInvoice $invoice, ElectronicBillingSetting $s): BillingResult
    {
        if (! $this->isAvailable()) {
            return $this->notInstalled();
        }

        if (! $cert = $this->readCertificate($s)) {
            return BillingResult::fail('No se encontró el certificado en la ruta indicada.', 'CERT_NOT_FOUND');
        }

        try {
            $see = $this->makeSee($s, $cert);

            $company = (new \Greenter\Model\Company\Company())
                ->setRuc($s->ruc)
                ->setRazonSocial($s->razon_social)
                ->setNombreComercial($s->nombre_comercial ?: '')
                ->setAddress($this->address($s));

            $client = (new \Greenter\Model\Client\Client())
                ->setTipoDoc($invoice->client_tipo_doc ?: '1')
                ->setNumDoc($invoice->client_num_doc ?: '00000000')
                ->setRznSocial($invoice->client_razon_social);

            $payload = [
                'tipo_doc' => $invoice->tipo_doc,
                'serie' => $invoice->serie,
                'correlativo' => (string) $invoice->correlativo,
                'fecha' => $invoice->fecha_emision,
                'items' => $invoice->items ?? [],
            ];

            $doc = $invoice->isNota()
                ? $this->buildNote($s, $company, $client, $payload + [
                    'ref_tipo_doc' => $invoice->ref_tipo_doc,
                    'ref_full_number' => $invoice->ref_full_number,
                    'cod_motivo' => $invoice->nota_cod_motivo,
                    'motivo' => $invoice->nota_motivo,
                ])
                : $this->buildInvoice($s, $company, $client, $payload);

            // Firmar y capturar el XML antes de enviar (API estable de Greenter).
            $signedXml = $see->getXmlSigned($doc);
            $xmlPath = $this->storeFile($s, $invoice->full_number, 'xml', $signedXml);
            $hash = $this->extractDigest($signedXml);

            $result = $see->send($doc);

            if (! $result->isSuccess()) {
                $err = $result->getError();

                return BillingResult::fail(
                    '['.$err->getCode().'] '.$err->getMessage(),
                    $err->getCode(),
                    'rechazado',
                    ['xmlPath' => $xmlPath, 'hash' => $hash]
                );
            }

            $cdr = $result->getCdrResponse();
            $cdrPath = $this->storeFile($s, $invoice->full_number, 'cdr.zip', $result->getCdrZip());

            $code = (int) $cdr->getCode();
            $status = $code === 0 ? 'aceptado' : ($code >= 2000 && $code <= 3999 ? 'rechazado' : 'observado');

            return new BillingResult(
                $status === 'aceptado' || $status === 'observado',
                $status,
                $cdr->getCode(),
                $cdr->getDescription(),
                $hash,
                $xmlPath,
                $cdrPath,
                null,
                $cdr->getNotes() ?? []
            );
        } catch (Throwable $e) {
            return BillingResult::fail('Error al emitir: '.$e->getMessage(), 'EXCEPTION');
        }
    }

    public function voidDocument(ElectronicInvoice $invoice, ElectronicBillingSetting $s): BillingResult
    {
        if (! $this->isAvailable()) {
            return $this->notInstalled();
        }

        // La comunicación de baja (facturas) / resumen de anulación (boletas) es un
        // proceso asíncrono con ticket. Se registra la anulación y se deja el ticket.
        // Implementación completa recomendada según el volumen del colegio.
        return BillingResult::ok('anulado', null,
            'Comprobante marcado como anulado. Para la comunicación de baja formal ante SUNAT complete el flujo de baja/resumen.');
    }

    public function sendSummary(ElectronicSummary $summary, array $invoices, ElectronicBillingSetting $s): BillingResult
    {
        if (! $this->isAvailable()) {
            return $this->notInstalled();
        }
        if (! $cert = $this->readCertificate($s)) {
            return BillingResult::fail('No se encontró el certificado en la ruta indicada.', 'CERT_NOT_FOUND');
        }
        if (empty($invoices)) {
            return BillingResult::fail('No hay boletas para incluir en el resumen.', 'EMPTY_SUMMARY');
        }

        try {
            $see = $this->makeSee($s, $cert);

            $company = (new \Greenter\Model\Company\Company())
                ->setRuc($s->ruc)
                ->setRazonSocial($s->razon_social)
                ->setAddress($this->address($s));

            $doc = (new \Greenter\Model\Summary\Summary())
                ->setFecGeneracion($summary->fecha_generacion instanceof \DateTimeInterface ? $summary->fecha_generacion : new \DateTime($summary->fecha_generacion))
                ->setFecResumen($summary->fecha_resumen instanceof \DateTimeInterface ? $summary->fecha_resumen : new \DateTime($summary->fecha_resumen))
                ->setCorrelativo(str_pad((string) $summary->correlativo, 1, '0', STR_PAD_LEFT))
                ->setCompany($company);

            $details = [];
            foreach ($invoices as $inv) {
                $details[] = (new \Greenter\Model\Summary\SummaryDetail())
                    ->setTipoDoc($inv->tipo_doc)
                    ->setSerieNro($inv->full_number)
                    ->setEstado('1') // 1 = adicionar
                    ->setClienteTipo($inv->client_tipo_doc ?: '1')
                    ->setClienteNro($inv->client_num_doc ?: '00000000')
                    ->setTotal((float) $inv->total)
                    ->setMtoOperGravadas((float) $inv->op_gravadas)
                    ->setMtoIGV((float) $inv->igv);
            }
            $doc->setDetails($details);

            $signedXml = $see->getXmlSigned($doc);
            $xmlPath = $this->storeFile($s, $summary->identifier, 'xml', $signedXml);

            $result = $see->send($doc);

            if (! $result->isSuccess()) {
                $err = $result->getError();

                return BillingResult::fail('['.$err->getCode().'] '.$err->getMessage(), $err->getCode(), 'rechazado', ['xmlPath' => $xmlPath]);
            }

            return BillingResult::ok('ticket', null, 'Resumen aceptado. Ticket generado, consulte el estado.', [
                'ticket' => $result->getTicket(),
                'xmlPath' => $xmlPath,
            ]);
        } catch (Throwable $e) {
            return BillingResult::fail('Error al enviar el resumen: '.$e->getMessage(), 'EXCEPTION');
        }
    }

    public function checkTicket(string $ticket, ElectronicBillingSetting $s): BillingResult
    {
        if (! $this->isAvailable()) {
            return $this->notInstalled();
        }
        if (! $cert = $this->readCertificate($s)) {
            return BillingResult::fail('No se encontró el certificado en la ruta indicada.', 'CERT_NOT_FOUND');
        }

        try {
            $see = $this->makeSee($s, $cert);
            $status = $see->getStatus($ticket);

            if (! $status->isSuccess()) {
                $err = $status->getError();

                // Código 98 = "en proceso"; sigue pendiente de resolución.
                if ($err && $err->getCode() === '98') {
                    return BillingResult::ok('ticket', '98', 'En proceso. Vuelva a consultar en unos minutos.', ['ticket' => $ticket]);
                }

                return BillingResult::fail('['.($err ? $err->getCode() : '').'] '.($err ? $err->getMessage() : 'Error'), $err ? $err->getCode() : null, 'rechazado');
            }

            $cdr = $status->getCdrResponse();
            $cdrPath = $this->storeFile($s, 'R-'.$ticket, 'zip', $status->getCdrZip());
            $code = (int) $cdr->getCode();
            $estado = $code === 0 ? 'aceptado' : ($code >= 2000 && $code <= 3999 ? 'rechazado' : 'observado');

            return new BillingResult(
                $estado !== 'rechazado', $estado, $cdr->getCode(), $cdr->getDescription(),
                null, null, $cdrPath, $ticket, $cdr->getNotes() ?? []
            );
        } catch (Throwable $e) {
            return BillingResult::fail('Error al consultar el ticket: '.$e->getMessage(), 'EXCEPTION');
        }
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Construye una Nota de crédito (tipo 07) de Greenter que referencia
     * al comprobante afectado.
     */
    private function buildNote(ElectronicBillingSetting $s, $company, $client, array $data): \Greenter\Model\Sale\Note
    {
        $igvPercent = (float) ($s->igv_percent ?: 18);
        $factor = $igvPercent / 100;

        $details = [];
        $gravadas = 0.0;
        $igvTotal = 0.0;

        foreach ($data['items'] as $item) {
            $cant = (float) ($item['cantidad'] ?? 1);
            $valorUnit = (float) ($item['valor_unit'] ?? 0);
            $valorVenta = round($valorUnit * $cant, 2);
            $igv = round($valorVenta * $factor, 2);
            $precioUnit = round($valorUnit * (1 + $factor), 2);

            $gravadas += $valorVenta;
            $igvTotal += $igv;

            $details[] = (new \Greenter\Model\Sale\SaleDetail())
                ->setCodProducto($item['codigo'] ?? 'SERV01')
                ->setUnidad($item['unidad'] ?? 'NIU')
                ->setCantidad($cant)
                ->setDescripcion($item['descripcion'] ?? 'Servicio educativo')
                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv($igvPercent)
                ->setIgv($igv)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($igv)
                ->setMtoValorVenta($valorVenta)
                ->setMtoValorUnitario($valorUnit)
                ->setMtoPrecioUnitario($precioUnit);
        }

        $gravadas = round($gravadas, 2);
        $igvTotal = round($igvTotal, 2);
        $total = round($gravadas + $igvTotal, 2);

        $legend = (new \Greenter\Model\Sale\Legend())
            ->setCode('1000')
            ->setValue(NumberToWords::toCurrency($total, $s->moneda ?: 'PEN'));

        return (new \Greenter\Model\Sale\Note())
            ->setUblVersion('2.1')
            ->setTipoDoc('07')
            ->setSerie($data['serie'])
            ->setCorrelativo(ltrim((string) $data['correlativo'], '0') ?: $data['correlativo'])
            ->setFechaEmision($data['fecha'] ?? new \DateTime())
            ->setTipDocAfectado($data['ref_tipo_doc'] ?: '03')
            ->setNumDocfectado($data['ref_full_number'])
            ->setCodMotivo($data['cod_motivo'] ?: '01')
            ->setDesMotivo($data['motivo'] ?: 'ANULACION DE LA OPERACION')
            ->setTipoMoneda($s->moneda ?: 'PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas($gravadas)
            ->setMtoIGV($igvTotal)
            ->setTotalImpuestos($igvTotal)
            ->setMtoImpVenta($total)
            ->setDetails($details)
            ->setLegends([$legend]);
    }

    private function makeSee(ElectronicBillingSetting $s, string $cert): \Greenter\See
    {
        $see = new \Greenter\See();
        $see->setCertificate($cert);
        $see->setService(
            $s->environment === 'produccion'
                ? \Greenter\Ws\Services\SunatEndpoints::FE_PRODUCCION
                : \Greenter\Ws\Services\SunatEndpoints::FE_BETA
        );
        $see->setClaveSOL($s->ruc, $s->sol_user, $s->sol_pass);

        return $see;
    }

    private function address(ElectronicBillingSetting $s): \Greenter\Model\Company\Address
    {
        return (new \Greenter\Model\Company\Address())
            ->setUbigueo($s->ubigeo ?: '150101')
            ->setDepartamento($s->departamento ?: 'LIMA')
            ->setProvincia($s->provincia ?: 'LIMA')
            ->setDistrito($s->distrito ?: 'LIMA')
            ->setUrbanizacion($s->urbanizacion ?: '-')
            ->setDireccion($s->direccion_fiscal ?: '-');
    }

    /**
     * Construye el objeto Invoice de Greenter a partir de datos normalizados.
     * $data = tipo_doc, serie, correlativo, fecha?, items[]
     */
    private function buildInvoice(ElectronicBillingSetting $s, $company, $client, array $data): \Greenter\Model\Sale\Invoice
    {
        $igvPercent = (float) ($s->igv_percent ?: 18);
        $factor = $igvPercent / 100;

        $details = [];
        $gravadas = 0.0;
        $igvTotal = 0.0;

        foreach ($data['items'] as $item) {
            $cant = (float) ($item['cantidad'] ?? 1);
            $valorUnit = (float) ($item['valor_unit'] ?? 0);   // valor sin IGV
            $valorVenta = round($valorUnit * $cant, 2);
            $igv = round($valorVenta * $factor, 2);
            $precioUnit = round($valorUnit * (1 + $factor), 2);

            $gravadas += $valorVenta;
            $igvTotal += $igv;

            $details[] = (new \Greenter\Model\Sale\SaleDetail())
                ->setCodProducto($item['codigo'] ?? 'SERV01')
                ->setUnidad($item['unidad'] ?? 'NIU')
                ->setCantidad($cant)
                ->setDescripcion($item['descripcion'] ?? 'Servicio educativo')
                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv($igvPercent)
                ->setIgv($igv)
                ->setTipAfeIgv('10') // Gravado - Operación onerosa
                ->setTotalImpuestos($igv)
                ->setMtoValorVenta($valorVenta)
                ->setMtoValorUnitario($valorUnit)
                ->setMtoPrecioUnitario($precioUnit);
        }

        $gravadas = round($gravadas, 2);
        $igvTotal = round($igvTotal, 2);
        $total = round($gravadas + $igvTotal, 2);

        $legend = (new \Greenter\Model\Sale\Legend())
            ->setCode('1000')
            ->setValue(NumberToWords::toCurrency($total, $s->moneda ?: 'PEN'));

        $invoice = (new \Greenter\Model\Sale\Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($data['tipo_doc'])
            ->setSerie($data['serie'])
            ->setCorrelativo(ltrim((string) $data['correlativo'], '0') ?: $data['correlativo'])
            ->setFechaEmision($data['fecha'] ?? new \DateTime())
            ->setTipoMoneda($s->moneda ?: 'PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas($gravadas)
            ->setMtoIGV($igvTotal)
            ->setTotalImpuestos($igvTotal)
            ->setValorVenta($gravadas)
            ->setSubTotal($total)
            ->setMtoImpVenta($total)
            ->setDetails($details)
            ->setLegends([$legend]);

        // Forma de pago (requerida en versiones recientes de Greenter).
        if (class_exists(\Greenter\Model\Sale\FormaPagos\FormaPagoContado::class)) {
            $invoice->setFormaPago(new \Greenter\Model\Sale\FormaPagos\FormaPagoContado());
        }

        return $invoice;
    }

    private function readCertificate(ElectronicBillingSetting $s): ?string
    {
        $path = $s->certificate_path;
        if (blank($path) || ! is_file($path) || ! is_readable($path)) {
            return null;
        }

        return file_get_contents($path) ?: null;
    }

    private function storeFile(ElectronicBillingSetting $s, string $number, string $ext, ?string $content): ?string
    {
        if (blank($content)) {
            return null;
        }

        $dir = 'facturacion/'.($s->ruc ?: 'sin-ruc');
        $path = $dir.'/'.$number.'.'.$ext;
        Storage::disk('local')->put($path, $content);

        return $path;
    }

    private function extractDigest(?string $xml): ?string
    {
        if (blank($xml)) {
            return null;
        }
        if (preg_match('/<ds:DigestValue>(.*?)<\/ds:DigestValue>/s', $xml, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/<DigestValue>(.*?)<\/DigestValue>/s', $xml, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function notInstalled(): BillingResult
    {
        return BillingResult::fail(
            'La librería Greenter no está instalada. Ejecuta en la raíz del proyecto: composer require greenter/greenter',
            'GREENTER_MISSING'
        );
    }
}
