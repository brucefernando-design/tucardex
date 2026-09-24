<?php

namespace App\Services\Facturacion;

/**
 * Resultado de una operación de facturación (emisión, prueba de conexión, baja).
 * DTO simple e inmutable que unifica la respuesta de cualquier driver.
 */
class BillingResult
{
    public function __construct(
        public bool $success,
        public string $status,           // aceptado | observado | rechazado | pendiente | error
        public ?string $code = null,     // código SUNAT o interno
        public ?string $message = null,  // descripción legible
        public ?string $hash = null,     // DigestValue del XML firmado
        public ?string $xmlPath = null,
        public ?string $cdrPath = null,
        public ?string $ticket = null,
        public array $notes = [],        // observaciones (warnings) de SUNAT
    ) {
    }

    public static function ok(string $status, ?string $code = null, ?string $message = null, array $extra = []): self
    {
        return new self(true, $status, $code, $message, ...static::extra($extra));
    }

    public static function fail(string $message, ?string $code = null, string $status = 'error', array $extra = []): self
    {
        return new self(false, $status, $code, $message, ...static::extra($extra));
    }

    public static function pending(string $message = 'Comprobante generado, pendiente de envío a SUNAT.'): self
    {
        return new self(true, 'pendiente', null, $message);
    }

    private static function extra(array $extra): array
    {
        return [
            'hash' => $extra['hash'] ?? null,
            'xmlPath' => $extra['xmlPath'] ?? null,
            'cdrPath' => $extra['cdrPath'] ?? null,
            'ticket' => $extra['ticket'] ?? null,
            'notes' => $extra['notes'] ?? [],
        ];
    }
}
