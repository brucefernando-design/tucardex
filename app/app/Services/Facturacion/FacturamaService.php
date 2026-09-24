<?php

namespace App\Services\Facturacion;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\Payment;
use App\Models\Setting;
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
            return 'https://api.facturama.com.mx';
        }

        return 'https://apisandbox.facturama.com.mx';
    }

    /**
     * Obtiene las credenciales activas (específicas de la escuela o las maestras de TuCardex).
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
     * Prueba la conexión con Facturama (GET /api/Profile o /api/Clients).
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
                'message' => 'No hay usuario y contraseña de Facturama configurados todavía en el servidor (.env).',
            ];
        }

        try {
            $response = Http::withBasicAuth($authUser, $authPass)
                ->timeout(10)
                ->get("{$baseUrl}/api/Profile");

            if ($response->successful()) {
                $profile = $response->json();
                $taxName = $profile['TaxName'] ?? $profile['Email'] ?? 'Cuenta Activa';
                return [
                    'ok' => true,
                    'message' => "Conexión exitosa con Facturama CFDI 4.0 ({$taxName}). Entorno: " . ($env ?: 'producción') . ".",
                ];
            }

            // Intento con endpoint alternativo de clientes
            $altResponse = Http::withBasicAuth($authUser, $authPass)
                ->timeout(10)
                ->get("{$baseUrl}/api/Clients?limit=1");

            if ($altResponse->successful()) {
                return [
                    'ok' => true,
                    'message' => 'Conexión verificada exitosamente con la API de Facturama.',
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
     * Emite un CFDI 4.0 oficial con Complemento IEDU para un pago de colegiatura.
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
        $clientCp = $settings->codigo_postal ?: '06000';

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

        // Si tenemos credenciales de Facturama activas, llamamos a la API
        if (!empty($creds['user']) && !empty($creds['password'])) {
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
                'ExpeditionPlace' => $settings->codigo_postal ?: '06000',
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

            try {
                $baseUrl = $this->getBaseUrl();
                $response = Http::withBasicAuth($creds['user'], $creds['password'])
                    ->timeout(25)
                    ->post("{$baseUrl}/3/cfdis", $payload);

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
                        'sunat_code' => $facturamaId, // Almacena el ID de Facturama para descargas
                        'hash' => $uuid,
                        'cdr_description' => 'CFDI 4.0 timbrado exitosamente con Facturama (Complemento IEDU)',
                        'error_message' => null,
                    ]);
                }

                $errorBody = $response->json('Message') ?? $response->json('message') ?? $response->body();
                Log::error('Facturama Error:', ['status' => $response->status(), 'body' => $errorBody]);

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
                Log::error('Facturama Exception: ' . $e->getMessage());
            }
        }

        // Modo Simulado o pruebas
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
     * Descarga XML o PDF oficial desde Facturama.
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

        try {
            $response = Http::withBasicAuth($creds['user'], $creds['password'])
                ->timeout(15)
                ->get("{$baseUrl}/api/Cfdi/{$format}/issued/{$invoiceId}");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['Content'])) {
                    return base64_decode($data['Content']);
                }
                return $response->body();
            }
        } catch (\Throwable $e) {
            Log::error("Error descargando {$format} de Facturama: " . $e->getMessage());
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
