<?php

namespace App\Services\Facturacion\Support;

/**
 * Convierte un importe numérico a su representación en letras (español),
 * en el formato exigido por SUNAT para la leyenda 1000, por ejemplo:
 *   1180.50  ->  "SON MIL CIENTO OCHENTA CON 50/100 SOLES"
 */
class NumberToWords
{
    private const UNIDADES = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];

    private const DIEZ_A_DIECINUEVE = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];

    private const DECENAS = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];

    private const CENTENAS = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    public static function toCurrency(float $amount, string $currency = 'PEN'): string
    {
        $moneda = match (strtoupper($currency)) {
            'USD' => 'DOLARES AMERICANOS',
            'EUR' => 'EUROS',
            default => 'SOLES',
        };

        $entero = (int) floor($amount);
        $centavos = (int) round(($amount - $entero) * 100);

        return 'SON '.static::words($entero).' CON '.str_pad((string) $centavos, 2, '0', STR_PAD_LEFT).'/100 '.$moneda;
    }

    public static function words(int $n): string
    {
        if ($n === 0) {
            return 'CERO';
        }

        if ($n < 0) {
            return 'MENOS '.static::words(abs($n));
        }

        $out = '';

        if ($n >= 1_000_000) {
            $millones = intdiv($n, 1_000_000);
            $out .= ($millones === 1 ? 'UN MILLON' : static::words($millones).' MILLONES').' ';
            $n %= 1_000_000;
        }

        if ($n >= 1000) {
            $miles = intdiv($n, 1000);
            $out .= ($miles === 1 ? 'MIL' : static::words($miles).' MIL').' ';
            $n %= 1000;
        }

        $out .= static::hundreds($n);

        return trim(preg_replace('/\s+/', ' ', $out));
    }

    private static function hundreds(int $n): string
    {
        if ($n === 0) {
            return '';
        }
        if ($n === 100) {
            return 'CIEN';
        }

        $out = self::CENTENAS[intdiv($n, 100)].' ';
        $out .= static::tens($n % 100);

        return trim($out);
    }

    private static function tens(int $n): string
    {
        if ($n === 0) {
            return '';
        }
        if ($n < 10) {
            return self::UNIDADES[$n];
        }
        if ($n < 20) {
            return self::DIEZ_A_DIECINUEVE[$n - 10];
        }
        if ($n < 30) {
            return $n === 20 ? 'VEINTE' : 'VEINTI'.self::UNIDADES[$n - 20];
        }

        $dec = self::DECENAS[intdiv($n, 10)];
        $u = $n % 10;

        return $u === 0 ? $dec : $dec.' Y '.self::UNIDADES[$u];
    }
}
