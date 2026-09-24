<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade el soporte de notas de crédito (referencia al documento afectado y
 * motivo) a los comprobantes, el vínculo con el resumen diario, y el
 * interruptor "boletas por resumen" en la configuración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->string('ref_tipo_doc', 2)->nullable()->after('tipo_doc');    // tipo del doc afectado (01/03)
            $table->string('ref_full_number', 20)->nullable()->after('ref_tipo_doc'); // F001-00000001
            $table->string('nota_cod_motivo', 2)->nullable()->after('ref_full_number'); // 01, 07, ...
            $table->string('nota_motivo')->nullable()->after('nota_cod_motivo');
            $table->foreignId('summary_id')->nullable()->after('payment_id')
                ->constrained('electronic_summaries')->nullOnDelete();
        });

        Schema::table('electronic_billing_settings', function (Blueprint $table) {
            $table->boolean('boletas_por_resumen')->default(false)->after('auto_emit');
        });
    }

    public function down(): void
    {
        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('summary_id');
            $table->dropColumn(['ref_tipo_doc', 'ref_full_number', 'nota_cod_motivo', 'nota_motivo']);
        });

        Schema::table('electronic_billing_settings', function (Blueprint $table) {
            $table->dropColumn('boletas_por_resumen');
        });
    }
};
