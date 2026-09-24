<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comprobantes de Pago Electrónicos emitidos ante SUNAT.
 * Cada fila es una boleta / factura / nota de crédito con su estado,
 * la respuesta de SUNAT (CDR) y las rutas de los archivos generados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();

            // Identificación del comprobante
            $table->string('tipo_doc', 2)->default('03');   // 01 factura, 03 boleta, 07 NC, 08 ND
            $table->string('serie', 4);
            $table->unsignedInteger('correlativo');
            $table->string('full_number', 20);              // F001-00000001
            $table->dateTime('fecha_emision');
            $table->string('moneda', 3)->default('PEN');

            // Cliente / adquirente
            $table->string('client_tipo_doc', 1)->default('1'); // 6 RUC, 1 DNI, 0 sin doc
            $table->string('client_num_doc', 15)->nullable();
            $table->string('client_razon_social');
            $table->string('client_direccion')->nullable();

            // Importes
            $table->decimal('op_gravadas', 12, 2)->default(0);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->json('items')->nullable();              // snapshot de líneas

            // Estado y respuesta SUNAT
            $table->string('estado', 20)->default('pendiente');
            // pendiente | enviando | aceptado | observado | rechazado | anulado | error
            $table->string('sunat_code')->nullable();
            $table->string('sunat_description')->nullable();
            $table->string('hash')->nullable();             // DigestValue del XML firmado
            $table->string('ticket')->nullable();           // ticket de baja/resumen
            $table->text('error_message')->nullable();

            // Archivos
            $table->string('xml_path')->nullable();
            $table->string('cdr_path')->nullable();
            $table->string('pdf_path')->nullable();

            $table->timestamps();

            $table->index(['school_id', 'tipo_doc', 'serie']);
            $table->unique(['school_id', 'serie', 'correlativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_invoices');
    }
};
