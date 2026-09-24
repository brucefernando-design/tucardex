<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use App\Services\Tenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ElectronicBillingSetting extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'enabled', 'auto_emit', 'driver', 'pac_driver', 'environment',
        'rfc', 'razon_social', 'nombre_comercial', 'direccion_fiscal', 'codigo_postal',
        'regimen_fiscal', 'pac_api_key', 'clave_prod_serv', 'clave_unidad', 'objeto_imp',
        'ruc', 'ubigeo', 'departamento', 'provincia', 'distrito', 'urbanizacion',
        'sol_user', 'sol_pass', 'certificate_path', 'certificate_password',
        'serie_factura', 'serie_boleta', 'serie_nc_factura', 'serie_nc_boleta',
        'igv_percent', 'moneda', 'boletas_por_resumen',
    ];

    protected $casts = [
        'enabled'             => 'boolean',
        'auto_emit'           => 'boolean',
        'boletas_por_resumen' => 'boolean',
        'igv_percent'         => 'decimal:2',
        // Llaves de timbrado PAC cifradas con AES-256-CBC
        'pac_api_key'         => 'encrypted',
        'pac_api_secret'      => 'encrypted',
        // 'rfc' no requiere cifrado (RFC es dato publico)
    ];

    public static function current(): self
    {
        $tenancy = app(Tenancy::class);
        $schoolId = $tenancy->id();

        if (!$schoolId) {
            $existing = static::withoutGlobalScopes()->first();
            if ($existing) {
                return $existing;
            }
        }

        return static::firstOrNew(
            ['school_id' => $schoolId],
            [
                'enabled' => false,
                'auto_emit' => true,
                'driver' => 'none',
                'pac_driver' => 'simulado',
                'environment' => 'beta',
                'regimen_fiscal' => '603',
                'clave_prod_serv' => '86121500',
                'clave_unidad' => 'E48',
                'objeto_imp' => '02',
                'serie_factura' => 'F',
                'serie_boleta' => 'REC',
                'serie_nc_factura' => 'NC',
                'serie_nc_boleta' => 'NCR',
                'igv_percent' => 0.00,
                'moneda' => 'MXN',
            ]
        );
    }
}
