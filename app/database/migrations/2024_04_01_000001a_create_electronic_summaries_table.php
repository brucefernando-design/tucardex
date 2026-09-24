<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Resúmenes diarios de boletas (RC) enviados a SUNAT. En producción las
 * boletas se informan agrupadas por día mediante un Resumen Diario que
 * devuelve un ticket asíncrono; luego se consulta el CDR con ese ticket.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();

            $table->date('fecha_generacion');   // fecha de emisión de las boletas
            $table->date('fecha_resumen');      // fecha de envío del resumen
            $table->unsignedInteger('correlativo'); // correlativo del resumen del día
            $table->string('identifier', 30);   // RC-YYYYMMDD-N

            $table->string('estado', 20)->default('pendiente');
            // pendiente | enviando | ticket | aceptado | observado | rechazado | error
            $table->string('ticket')->nullable();
            $table->string('sunat_code')->nullable();
            $table->string('sunat_description')->nullable();
            $table->text('error_message')->nullable();

            $table->unsignedInteger('total_docs')->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->string('xml_path')->nullable();
            $table->string('cdr_path')->nullable();

            $table->timestamps();

            $table->index(['school_id', 'fecha_generacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_summaries');
    }
};
