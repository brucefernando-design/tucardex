<?php

namespace Tests\Unit;

use App\Services\Facturacion\Support\NumberToWords;
use PHPUnit\Framework\TestCase;

class NumberToWordsTest extends TestCase
{
    public function test_convierte_enteros_a_letras(): void
    {
        $this->assertSame('CERO', NumberToWords::words(0));
        $this->assertSame('QUINCE', NumberToWords::words(15));
        $this->assertSame('VEINTIUNO', NumberToWords::words(21));
        $this->assertSame('CIEN', NumberToWords::words(100));
        $this->assertSame('CIENTO OCHENTA', NumberToWords::words(180));
        $this->assertSame('MIL CIENTO OCHENTA', NumberToWords::words(1180));
        $this->assertSame('UN MILLON', NumberToWords::words(1000000));
    }

    public function test_formato_de_moneda_soles(): void
    {
        $this->assertSame(
            'SON MIL CIENTO OCHENTA CON 50/100 SOLES',
            NumberToWords::toCurrency(1180.50, 'PEN')
        );

        $this->assertSame(
            'SON CIENTO DIECIOCHO CON 00/100 SOLES',
            NumberToWords::toCurrency(118.00, 'PEN')
        );
    }

    public function test_formato_de_moneda_otras_divisas(): void
    {
        $this->assertStringEndsWith('DOLARES AMERICANOS', NumberToWords::toCurrency(50.00, 'USD'));
        $this->assertStringEndsWith('EUROS', NumberToWords::toCurrency(50.00, 'EUR'));
    }
}
