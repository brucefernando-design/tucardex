<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuración de Facturación Electrónica (SUNAT · Perú).
 * Un registro por colegio (tenant). Guarda el estado de emisión,
 * los datos del emisor y las credenciales SUNAT (Clave SOL + certificado).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_billing_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();

            // Estado y modo
            $table->boolean('enabled')->default(false);          // Habilitar facturación electrónica
            $table->boolean('auto_emit')->default(true);         // Emitir automáticamente al registrar el pago
            $table->string('driver', 30)->default('none');       // none | greenter
            $table->string('environment', 20)->default('beta');  // beta (homologación) | produccion

            // Datos del emisor (aparecen en el comprobante)
            $table->string('ruc', 11)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('nombre_comercial')->nullable();
            $table->string('direccion_fiscal')->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('departamento', 60)->nullable();
            $table->string('provincia', 60)->nullable();
            $table->string('distrito', 60)->nullable();
            $table->string('urbanizacion', 120)->nullable();

            // Credenciales SUNAT
            $table->text('sol_user')->nullable();          // Usuario Clave SOL (encriptado)
            $table->text('sol_pass')->nullable();          // Clave SOL (encriptado)
            $table->string('certificate_path')->nullable();// Ruta del certificado .pem / .pfx
            $table->text('certificate_password')->nullable(); // Clave del certificado (encriptado)

            // Credenciales GRE / API REST (opcional, para guía de remisión / futuro)
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();

            // Series y parámetros de emisión
            $table->string('serie_factura', 4)->default('F001');
            $table->string('serie_boleta', 4)->default('B001');
            $table->string('serie_nc_factura', 4)->default('FC01'); // Nota de crédito de factura
            $table->string('serie_nc_boleta', 4)->default('BC01');  // Nota de crédito de boleta
            $table->decimal('igv_percent', 5, 2)->default(18.00);
            $table->string('moneda', 3)->default('PEN');

            $table->timestamps();

            $table->unique('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_billing_settings');
    }
};
