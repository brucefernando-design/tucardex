<?php

namespace App\Services;

use App\Models\DocumentVerification;
use App\Models\Setting;
use App\Models\Student;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
    /**
     * Genera un código QR en formato data URI (base64) compatible con DomPDF.
     * Compatible con Endroid QrCode v4, v5 y v6+.
     */
    public static function generateDataUri(string $content, int $size = 120, int $margin = 4): ?string
    {
        if (! class_exists(QrCode::class) || ! class_exists(PngWriter::class)) {
            return null;
        }

        try {
            if (method_exists(QrCode::class, 'create')) {
                $qr = QrCode::create($content)
                    ->setSize($size)
                    ->setMargin($margin);
            } else {
                $qr = new QrCode(
                    data: $content,
                    size: $size,
                    margin: $margin
                );
            }

            $writer = new PngWriter();
            return $writer->write($qr)->getDataUri();
        } catch (\Throwable $e) {
            Log::warning('Error generando código QR: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra un documento oficial verificable y devuelve el Data URI del QR apuntando a la URL pública de verificación.
     */
    public static function generateVerifiedQr(
        Student $student,
        Setting $setting,
        string $docType,
        string $docTitle,
        ?string $folio = null,
        array $extraData = [],
        int $size = 120
    ): ?string {
        $verification = DocumentVerification::issueForStudent(
            $student,
            $setting,
            $docType,
            $docTitle,
            $folio,
            $extraData
        );

        $url = $verification->verification_url;

        return static::generateDataUri($url, $size);
    }
}
