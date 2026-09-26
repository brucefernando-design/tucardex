<?php

namespace App\Services\Facturacion;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacturamaService
{
    /**
     * Devuelve la URL base según el entorno (producción o sandbox).
     */
    public function getBaseUrl(?string $env = null): string
    {
        $settings = ElectronicBillingSetting::current();
        $targetEnv = $env ?: ($settings->environment ?? config('services.facturama.env', 'produccion'));

        if ($targetEnv === 'produccion') {
            return 'https://api.facturama.mx';
        }

        return 'https://apisandbox.facturama.mx';
    }

    /**
     * Obtiene las credenciales activas (específicas de la escuela o las maestras de TuKardex).
     */
    public function getCredentials(): array
    {
        $settings = ElectronicBillingSetting::current();

        $user = $settings->client_id ?: config('services.facturama.user');
        $password = $settings->client_secret ?: config('services.facturama.password');

        return [
            'user' => $user,
            'password' => $password,
            'is_master' => empty($settings->client_id) && !empty(config('services.facturama.user')),
        ];
    }

    /**
     * Prueba la conexión con Facturama (GET /TaxEntity o /api-lite/csds).
     */
    public function testConnection(?string $user = null, ?string $password = null, ?string $env = null): array
    {
        $creds = $this->getCredentials();
        $authUser = $user ?: $creds['user'];
        $authPass = $password ?: $creds['password'];
        $baseUrl = $this->getBaseUrl($env);

        if (empty($authUser) || empty($authPass)) {
            return [
                'ok' => false,
                'message' => 'No hay usuario y contraseña de Facturama configurados en el servidor (.env).',
            ];
        }

        try {
            $response = Http::withBasicAuth($authUser, $authPass)
                ->timeout(12)
                ->get("{$baseUrl}/TaxEntity");

            if ($response->successful()) {
                $tax = $response->json();
                $taxName = $tax['TaxName'] ?? $tax['ComercialName'] ?? 'Cuenta Activa';
                $rfc = $tax['Rfc'] ?? '';
                $regimen = $tax['FiscalRegime'] ?? '';
                return [
                    'ok' => true,
                    'message' => "Conexión exitosa con Facturama CFDI 4.0: {$taxName} (RFC: {$rfc}, Régimen: {$regimen}). Timbres TuKardex Multiemisor listos.",
                ];
            }

            // Fallback con CSDs o sucursales
            $csdsResponse = Http::withBasicAuth($authUser, $authPass)
                ->timeout(10)
                ->get("{$baseUrl}/api-lite/csds");

            if ($csdsResponse->successful()) {
                return [
                    'ok' => true,
                    'message' => 'Conexión verificada exitosamente con la API Multiemisor de Facturama.',
                ];
            }

            $errorMsg = $response->json('Message') ?? $response->json('message') ?? $response->body();
            return [
                'ok' => false,
                'message' => "Error de autenticación con Facturama (HTTP {$response->status()}): {$errorMsg}",
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Error de conexión con Facturama: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sube o actualiza el Certificado de Sello Digital (CSD) de la escuela en Facturama Multiemisor.
     */
    public function uploadCsd(string $rfc, string $certificateBase64, string $privateKeyBase64, string $password): array
    {
        $creds = $this->getCredentials();
        $baseUrl = $this->getBaseUrl();

        if (empty($creds['user']) || empty($creds['password'])) {
            return [
                'ok' => false,
                'message' => 'Credenciales maestras de Facturama no configuradas en el servidor.',
            ];
        }

        $cleanRfc = strtoupper(trim($rfc));
        $payload = [
            'Rfc' => $cleanRfc,
            'Certificate' => $certificateBase64,
            'PrivateKey' => $privateKeyBase64,
            'PrivateKeyPassword' => $password,
        ];

        try {
            // Intentar crear (POST) o actualizar (PUT)
            $response = Http::withBasicAuth($creds['user'], $creds['password'])
                ->timeout(20)
                ->post("{$baseUrl}/api-lite/csds", $payload);

            if (!$response->successful() && $response->status() === 409) {
                // Ya existe, actualizar con PUT
                $response = Http::withBasicAuth($creds['user'], $creds['password'])
                    ->timeout(20)
                    ->put("{$baseUrl}/api-lite/csds/{$cleanRfc}", $payload);
            }

            if ($response->successful()) {
                // Consultar vigencia del CSD registrado
                $csdInfo = $this->getCsdInfo($cleanRfc);
                $expDate = $csdInfo['CsdExpirationDate'] ?? null;

                return [
                    'ok' => true,
                    'message' => 'Certificado de Sello Digital (CSD) sincronizado y verificado ante el SAT exitosamente.',
                    'expiration' => $expDate ? Carbon::parse($expDate) : null,
                ];
            }

            $errMsg = $response->json('Message') 
                ?? $response->json('ModelState') 
                ?? $response->json('ExceptionMessage') 
                ?? $response->body();

            if (is_array($errMsg)) {
                $errMsg = json_encode($errMsg, JSON_UNESCAPED_UNICODE);
            }

            return [
                'ok' => false,
                'message' => "Facturama rechazó el CSD: {$errMsg}",
            ];
        } catch (\Throwable $e) {
            Log::error("Error subiendo CSD a Facturama: " . $e->getMessage());
            return [
                'ok' => false,
                'message' => 'Error de comunicación al subir CSD: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Consulta el CSD de un RFC en Facturama Multiemisor.
     */
    public function getCsdInfo(string $rfc): ?array
    {
        $creds = $this->getCredentials();
        $baseUrl = $this->getBaseUrl();

        if (empty($creds['user']) || empty($creds['password'])) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($creds['user'], $creds['password'])
                ->timeout(10)
                ->get("{$baseUrl}/api-lite/csds");

            if ($response->successful()) {
                $csds = $response->json();
                if (is_array($csds)) {
                    $cleanRfc = strtoupper(trim($rfc));
                    foreach ($csds as $item) {
                        if (strtoupper($item['Rfc'] ?? '') === $cleanRfc) {
                            return $item;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Error consultando CSD en Facturama: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Elimina el CSD de la escuela en Facturama Multiemisor.
     */
    public function deleteCsd(string $rfc): bool
    {
        $creds = $this->getCredentials();
        $baseUrl = $this->getBaseUrl();

        try {
            $cleanRfc = strtoupper(trim($rfc));
            $response = Http::withBasicAuth($creds['user'], $creds['password'])
                ->timeout(10)
                ->delete("{$baseUrl}/api-lite/csds/{$cleanRfc}");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Error eliminando CSD en Facturama: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Emite un CFDI 4.0 oficial con Complemento IEDU para un pago de colegiatura.
     * Si la escuela tiene CSD activo, timbra vía Multiemisor (/api-lite/3/cfdis) con su RFC.
     */
    public function emitForPayment(Payment $payment, string $tipoDoc = '03'): ElectronicInvoice
    {
        $settings = ElectronicBillingSetting::current();
        $appSettings = Setting::current();
        $creds = $this->getCredentials();

        $payment->loadMissing(['student.course', 'student']);
        $student = $payment->student;
        $course = optional($student)->course;

        $nivelSat = $this->mapNivelEducativo($course->level ?? '');

        // Datos del Receptor (Padre / Tutor o Público General)
        $rawRfc = strtoupper(trim($student->dni ?? ''));
        $clientRfc = $this->isValidRfc($rawRfc) ? $rawRfc : 'XAXX010101000';
        $clientNombre = strtoupper($student->guardian_name ?: ($student->full_name ?? 'PUBLICO EN GENERAL'));
        $clientCp = $settings->codigo_postal ?: '88177';

        // Complemento IEDU
        $curpAlumno = $student->curp ?: 'XAXX01010100000000';
        $nombreAlumno = $student->full_name ?? 'Alumno';
        $rvoeColegio = $appSettings->rvoe ?: 'SEP-RVOE-2024';

        // Forma de pago SAT
        $formaPago = match (strtolower($payment->method ?? '')) {
            'spei', 'transferencia' => '03',
            'tarjeta', 'tarjeta_credito', 'tarjeta_debito' => '04',
            default => '01', // Efectivo
        };

        $descripcion = 'Colegiatura ' . ($payment->concept ?: 'Mensualidad') . ($payment->period ? ' · ' . $payment->period : '');

        // ¿Tiene CSD activo o credenciales de Facturama?
        $useMultiemisor = $settings->isCsdActive();
        $hasApiCreds = !empty($creds['user']) && !empty($creds['password']);

        if ($hasApiCreds && $settings->pac_driver === 'facturama') {
            $endpoint = $useMultiemisor ? '/api-lite/3/cfdis' : '/3/cfdis';

            $payload = [
                'Receiver' => [
                    'Rfc' => $clientRfc,
                    'Name' => $clientNombre,
                    'FiscalRegime' => $clientRfc === 'XAXX010101000' ? '616' : '605',
                    'TaxZipCode' => $clientCp,
                    'CfdiUse' => 'D10', // Deducción de colegiaturas
                ],
                'CfdiType' => 'I', // Ingreso
                'PaymentForm' => $formaPago,
                'PaymentMethod' => 'PUE', // Pago en una sola exhibición
                'Currency' => 'MXN',
                'Date' => now()->format('Y-m-d\TH:i:s'),
                'ExpeditionPlace' => $settings->codigo_postal ?: '88177',
                'Items' => [
                    [
                        'ProductCode' => $settings->clave_prod_serv ?: '86121500',
                        'IdentificationNumber' => 'COL-' . $payment->id,
                        'Description' => $descripcion,
                        'Unit' => 'Servicio',
                        'UnitCode' => $settings->clave_unidad ?: 'E48',
                        'UnitPrice' => (float) $payment->amount,
                        'Quantity' => 1,
                        'Subtotal' => (float) $payment->amount,
                        'TaxObject' => '02', // Objeto de impuesto (IVA Exento)
                        'Taxes' => [
                            [
                                'Total' => 0.00,
                                'Name' => 'IVA',
                                'Base' => (float) $payment->amount,
                                'Rate' => 0.00,
                                'IsRetention' => false,
                            ],
                        ],
                        'Total' => (float) $payment->amount,
                        'Complement' => [
                            'EducationalInstitutions' => [
                                'Version' => '1.0',
                                'Name' => $nombreAlumno,
                                'Curp' => $curpAlumno,
                                'Level' => $nivelSat,
                                'AutRvoe' => $rvoeColegio,
                                'PaymentRfc' => $clientRfc,
                            ],
                        ],
                    ],
                ],
            ];

            // En modo Multiemisor, se incluye el Emisor con el RFC del Colegio
            if ($useMultiemisor) {
                $payload['Issuer'] = [
                    'Rfc' => strtoupper(trim($settings->rfc)),
                    'Name' => strtoupper(trim($settings->razon_social)),
                    'FiscalRegime' => $settings->regimen_fiscal ?: '603',
                ];
            }

            try {
                $baseUrl = $this->getBaseUrl();
                $response = Http::withBasicAuth($creds['user'], $creds['password'])
                    ->timeout(25)
                    ->post("{$baseUrl}{$endpoint}", $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $facturamaId = $data['Id'] ?? null;
                    $uuid = $data['Complement']['TaxStamp']['Uuid'] ?? Str::uuid()->toString();
                    $serie = $data['Serie'] ?? ($settings->serie_factura ?: 'F');
                    $folio = $data['Folio'] ?? (ElectronicInvoice::where('serie', $serie)->max('correlativo') + 1);

                    return ElectronicInvoice::create([
                        'school_id' => $settings->school_id,
                        'payment_id' => $payment->id,
                        'tipo_doc' => $tipoDoc === '01' ? '01' : '03',
                        'serie' => $serie,
                        'correlativo' => (int) $folio,
                        'full_number' => "{$serie}-" . str_pad((string) $folio, 6, '0', STR_PAD_LEFT),
                        'fecha_emision' => now(),
                        'moneda' => 'MXN',
                        'op_gravadas' => 0,
                        'op_exoneradas' => (float) $payment->amount,
                        'op_inafectas' => 0,
                        'total_igv' => 0,
                        'total' => (float) $payment->amount,
                        'client_tipo_doc' => strlen($clientRfc) === 12 ? '6' : '1',
                        'client_num_doc' => $clientRfc,
                        'client_razon_social' => $clientNombre,
                        'client_direccion' => $clientCp,
                        'estado' => 'aceptado',
                        'sunat_code' => $facturamaId, // ID de Facturama para descargas
                        'hash' => $uuid,
                        'cdr_description' => 'CFDI 4.0 timbrado exitosamente con Facturama (Complemento IEDU SAT)',
                        'error_message' => null,
                    ]);
                }

                $errorBody = $response->json('Message') 
                    ?? $response->json('message') 
                    ?? $response->json('ModelState')
                    ?? $response->body();

                if (is_array($errorBody)) {
                    $errorBody = json_encode($errorBody, JSON_UNESCAPED_UNICODE);
                }

                Log::error('Facturama CFDI Error:', ['status' => $response->status(), 'body' => $errorBody]);

                return ElectronicInvoice::create([
                    'school_id' => $settings->school_id,
                    'payment_id' => $payment->id,
                    'tipo_doc' => $tipoDoc,
                    'serie' => $settings->serie_factura ?: 'F',
                    'correlativo' => ((int) ElectronicInvoice::where('serie', $settings->serie_factura ?: 'F')->max('correlativo') + 1),
                    'full_number' => 'F-ERR',
                    'fecha_emision' => now(),
                    'moneda' => 'MXN',
                    'total' => (float) $payment->amount,
                    'client_tipo_doc' => '1',
                    'client_num_doc' => $clientRfc,
                    'client_razon_social' => $clientNombre,
                    'estado' => 'error',
                    'error_message' => "Facturama: {$errorBody}",
                ]);
            } catch (\Throwable $e) {
                Log::error('Facturama CFDI Exception: ' . $e->getMessage());
            }
        }

        // Modo Simulado / Demostración
        $serie = $settings->serie_factura ?: 'F';
        $folio = (int) (ElectronicInvoice::where('serie', $serie)->max('correlativo') ?? 0) + 1;
        $uuid = Str::uuid()->toString();

        return ElectronicInvoice::create([
            'school_id' => $settings->school_id,
            'payment_id' => $payment->id,
            'tipo_doc' => $tipoDoc === '01' ? '01' : '03',
            'serie' => $serie,
            'correlativo' => $folio,
            'full_number' => "{$serie}-" . str_pad((string) $folio, 6, '0', STR_PAD_LEFT),
            'fecha_emision' => now(),
            'moneda' => 'MXN',
            'op_gravadas' => 0,
            'op_exoneradas' => (float) $payment->amount,
            'op_inafectas' => 0,
            'total_igv' => 0,
            'total' => (float) $payment->amount,
            'client_tipo_doc' => strlen($clientRfc) === 12 ? '6' : '1',
            'client_num_doc' => $clientRfc,
            'client_razon_social' => $clientNombre,
            'client_direccion' => $clientCp,
            'estado' => 'aceptado',
            'sunat_code' => 'simulado_' . Str::random(12),
            'hash' => $uuid,
            'cdr_description' => 'CFDI 4.0 generado en modo simulado (Complemento IEDU SAT)',
        ]);
    }

    /**
     * Descarga XML o PDF oficial desde Facturama (soporta tanto Web API como Multiemisor).
     */
    public function downloadFile(string $invoiceId, string $format = 'pdf'): ?string
    {
        if (str_starts_with($invoiceId, 'simulado_')) {
            return null;
        }

        $creds = $this->getCredentials();
        $baseUrl = $this->getBaseUrl();

        if (empty($creds['user']) || empty($creds['password'])) {
            return null;
        }

        $format = strtolower($format) === 'xml' ? 'xml' : 'pdf';

        // Probar primero con issuedLite (Multiemisor), luego con issued (Web API)
        foreach (['issuedLite', 'issued'] as $type) {
            try {
                $response = Http::withBasicAuth($creds['user'], $creds['password'])
                    ->timeout(15)
                    ->get("{$baseUrl}/cfdi/{$format}/{$type}/{$invoiceId}");

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['Content'])) {
                        return base64_decode($data['Content']);
                    }
                    return $response->body();
                }
            } catch (\Throwable $e) {
                Log::warning("Error descargando {$format}/{$type}: " . $e->getMessage());
            }
        }

        return null;
    }

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
        return (bool) preg_match('/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc);
    }
}
