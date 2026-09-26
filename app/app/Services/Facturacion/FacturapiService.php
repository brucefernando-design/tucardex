<?php

namespace App\Services\Facturacion;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacturapiService
{
    protected string $baseUrl = 'https://www.facturapi.io/v2';

    /**
     * Prueba la conexión con la API de Facturapi utilizando la API Key configurada.
     */
    public function testConnection(?string $apiKey = null): array
    {
        $key = $apiKey ?: $this->getApiKey();

        if (empty($key)) {
            return ['ok' => false, 'message' => 'No hay API Key configurada en los ajustes de facturación.'];
        }

        try {
            $response = Http::withToken($key)
                ->timeout(10)
                ->get("{$this->baseUrl}/invoices", ['limit' => 1]);

            if ($response->successful()) {
                return ['ok' => true, 'message' => 'Conexión exitosa con Facturapi API v2 (SAT CFDI 4.0).'];
            }

            $error = $response->json('message') ?? $response->body();
            return ['ok' => false, 'message' => "Error de Facturapi: {$error}"];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    /**
     * Emite un CFDI 4.0 con Complemento IEDU para un pago de colegiatura.
     */
    public function emitForPayment(Payment $payment, string $tipoDoc = '03'): ElectronicInvoice
    {
        $settings = ElectronicBillingSetting::current();
        $appSettings = Setting::current();
        $apiKey = $settings->pac_api_key ?: config('services.facturapi.key');

        $payment->loadMissing(['student.course', 'student']);
        $student = $payment->student;
        $course = optional($student)->course;

        // Normalizar nivel educativo para catálogo oficial SAT IEDU
        $nivelSat = $this->mapNivelEducativo($course->level ?? '');

        // Datos del receptor (Padre / Tutor o Público en General)
        $rawRfc = strtoupper(trim($student->dni ?? ''));
        $clientRfc = $this->isValidRfc($rawRfc) ? $rawRfc : 'XAXX010101000';
        $clientNombre = $student->guardian_name ?: ($student->full_name ?? 'PÚBLICO EN GENERAL');
        $clientCp = $settings->codigo_postal ?: '06000';

        // Construir XML del Complemento Concepto IEDU (Requisito Anexo 20 SAT)
        $curpAlumno = $student->curp ?: 'XAXX01010100000000';
        $nombreAlumno = htmlspecialchars($student->full_name ?? 'Alumno', ENT_XML1, 'UTF-8');
        $rvoeColegio = htmlspecialchars($appSettings->rvoe ?: 'SEP-RVOE-PENDIENTE', ENT_XML1, 'UTF-8');

        $ieduXml = sprintf(
            '<iedu:instEducativas xmlns:iedu="http://www.sat.gob.mx/iedu" version="1.0" nombreAlumno="%s" CURP="%s" nivelEducativo="%s" autRVOE="%s" rfcPago="%s"/>',
            $nombreAlumno,
            $curpAlumno,
            $nivelSat,
            $rvoeColegio,
            $clientRfc
        );

        // Mapear forma de pago SAT
        $formaPago = match (strtolower($payment->method ?? '')) {
            'spei', 'transferencia' => '03',
            'tarjeta', 'tarjeta_credito', 'tarjeta_debito' => '04',
            default => '01', // Efectivo
        };

        $descripcion = 'Colegiatura' . ($payment->concept ? ' · ' . $payment->concept : '') . ($payment->period ? ' · ' . $payment->period : '');

        $payload = [
            'customer' => [
                'legal_name' => $clientNombre,
                'tax_id' => $clientRfc,
                'tax_system' => $clientRfc === 'XAXX010101000' ? '616' : '605',
                'address' => [
                    'zip' => $clientCp,
                ],
            ],
            'items' => [
                [
                    'quantity' => 1,
                    'product' => [
                        'description' => $descripcion,
                        'product_key' => $settings->clave_prod_serv ?: '86121500',
                        'price' => (float) $payment->amount,
                        'unit_key' => $settings->clave_unidad ?: 'E48',
                        'taxes' => [
                            [
                                'type' => 'IVA',
                                'rate' => 0,
                                'factor' => 'Exento',
                            ],
                        ],
                    ],
                    'complement' => $ieduXml,
                ],
            ],
            'use' => 'D10', // Pagos por servicios educativos (colegiaturas)
            'payment_form' => $formaPago,
        ];

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post("{$this->baseUrl}/invoices", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $invoiceId = $data['id'] ?? null;
                $uuid = $data['uuid'] ?? null;
                $folio = $data['folio_number'] ?? 1;
                $serie = $data['series'] ?? ($settings->serie_factura ?: 'F');

                return ElectronicInvoice::create([
                    'school_id' => $settings->school_id,
                    'payment_id' => $payment->id,
                    'tipo_doc' => $tipoDoc === '01' ? '01' : '03',
                    'serie' => $serie,
                    'correlativo' => $folio,
                    'full_number' => "{$serie}-" . str_pad((string) $folio, 6, '0', STR_PAD_LEFT),
                    'fecha_emision' => now(),
                    'moneda' => 'MXN',
                    'client_tipo_doc' => $clientRfc === 'XAXX010101000' ? '0' : '6',
                    'client_num_doc' => $clientRfc,
                    'client_razon_social' => $clientNombre,
                    'client_direccion' => optional($student)->address,
                    'op_gravadas' => 0.00,
                    'igv' => 0.00,
                    'total' => (float) $payment->amount,
                    'items' => $payload['items'],
                    'estado' => 'aceptado',
                    'hash' => $uuid,
                    'sunat_code' => $invoiceId,
                    'sunat_description' => 'CFDI 4.0 Timbrado SAT con Complemento IEDU',
                    'ticket' => $data['verification_url'] ?? null,
                    'xml_path' => "https://www.facturapi.io/v2/invoices/{$invoiceId}/xml",
                    'pdf_path' => "https://www.facturapi.io/v2/invoices/{$invoiceId}/pdf",
                ]);
            }

            $errBody = $response->json();
            $errMsg = $errBody['message'] ?? $response->body();
            Log::error('Facturapi error: ' . $errMsg, ['body' => $errBody]);

            return ElectronicInvoice::create([
                'school_id' => $settings->school_id,
                'payment_id' => $payment->id,
                'tipo_doc' => $tipoDoc,
                'serie' => $settings->serie_factura ?: 'F',
                'correlativo' => ElectronicInvoice::nextCorrelativo($settings->school_id, 'F'),
                'full_number' => 'ERR-' . now()->format('YmdHis'),
                'fecha_emision' => now(),
                'moneda' => 'MXN',
                'client_tipo_doc' => '0',
                'client_num_doc' => $clientRfc,
                'client_razon_social' => $clientNombre,
                'total' => (float) $payment->amount,
                'estado' => 'error',
                'error_message' => $errMsg,
            ]);
        } catch (\Throwable $e) {
            Log::error('Facturapi Exception: ' . $e->getMessage());

            return ElectronicInvoice::create([
                'school_id' => $settings->school_id,
                'payment_id' => $payment->id,
                'tipo_doc' => $tipoDoc,
                'serie' => $settings->serie_factura ?: 'F',
                'correlativo' => ElectronicInvoice::nextCorrelativo($settings->school_id, 'F'),
                'full_number' => 'ERR-' . now()->format('YmdHis'),
                'fecha_emision' => now(),
                'moneda' => 'MXN',
                'client_tipo_doc' => '0',
                'client_num_doc' => $clientRfc,
                'client_razon_social' => $clientNombre,
                'total' => (float) $payment->amount,
                'estado' => 'error',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cancela una factura ante el SAT via Facturapi.
     */
    public function cancel(ElectronicInvoice $invoice, string $motive = '02'): bool
    {
        $settings = ElectronicBillingSetting::current();
        $apiKey = $settings->pac_api_key ?: config('services.facturapi.key');
        $facturapiId = $invoice->sunat_code; // ID en Facturapi

        if (empty($facturapiId)) {
            $invoice->update(['estado' => 'anulado']);
            return true;
        }

        try {
            $response = Http::withToken($apiKey)
                ->delete("{$this->baseUrl}/invoices/{$facturapiId}", [
                    'motive' => $motive,
                ]);

            if ($response->successful()) {
                $invoice->update(['estado' => 'anulado']);
                return true;
            }

            Log::error('Facturapi Cancel error: ' . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error('Facturapi Cancel exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Descarga el archivo (pdf o xml) directamente desde Facturapi usando la API Key del servidor.
     */
    public function downloadFile(string $invoiceId, string $format = 'pdf'): ?string
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey) || empty($invoiceId)) {
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->get("{$this->baseUrl}/invoices/{$invoiceId}/{$format}");

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable $e) {
            Log::error("Error descargando {$format} de Facturapi: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Mapea el nivel escolar de TuKardex al catálogo oficial del SAT para IEDU.
     * Catálogo SAT: Preescolar, Primaria, Secundaria, Profesional Tecnico, Bachillerato o su equivalente
     */
    protected function mapNivelEducativo(string $level): string
    {
        $lvl = strtolower(trim($level));

        if (str_contains($lvl, 'pre') || str_contains($lvl, 'kinder') || str_contains($lvl, 'inicial')) {
            return 'Preescolar';
        }
        if (str_contains($lvl, 'prim')) {
            return 'Primaria';
        }
        if (str_contains($lvl, 'sec')) {
            return 'Secundaria';
        }
        if (str_contains($lvl, 'tec') || str_contains($lvl, 'profesional')) {
            return 'Profesional Tecnico';
        }

        // Default para preparatoria / bachillerato o general
        return 'Bachillerato o su equivalente';
    }

    protected function isValidRfc(?string $rfc): bool
    {
        if (empty($rfc)) {
            return false;
        }
        $rfc = strtoupper(trim($rfc));
        if ($rfc === 'XAXX010101000' || $rfc === 'XEXX010101000') {
            return true;
        }
        // RFC Persona Moral (12) o Fisica (13)
        return (bool) preg_match('/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc);
    }

    protected function getApiKey(): ?string
    {
        $settings = ElectronicBillingSetting::current();
        return $settings->pac_api_key ?: config('services.facturapi.key');
    }
}
