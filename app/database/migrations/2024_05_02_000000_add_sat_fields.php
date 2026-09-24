<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electronic_billing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('electronic_billing_settings', 'rfc')) {
                $table->string('rfc', 13)->nullable()->after('environment');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'codigo_postal')) {
                $table->string('codigo_postal', 5)->nullable()->after('direccion_fiscal');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'regimen_fiscal')) {
                $table->string('regimen_fiscal', 10)->default('603')->after('codigo_postal');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'pac_driver')) {
                $table->string('pac_driver', 30)->default('simulado')->after('driver');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'pac_api_key')) {
                $table->text('pac_api_key')->nullable()->after('pac_driver');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'clave_prod_serv')) {
                $table->string('clave_prod_serv', 10)->default('86121500')->after('pac_api_key');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'clave_unidad')) {
                $table->string('clave_unidad', 10)->default('E48')->after('clave_prod_serv');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'objeto_imp')) {
                $table->string('objeto_imp', 5)->default('01')->after('clave_unidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('electronic_billing_settings', function (Blueprint $table) {
            $table->dropColumn([
                'rfc', 'codigo_postal', 'regimen_fiscal', 'pac_driver', 
                'pac_api_key', 'clave_prod_serv', 'clave_unidad', 'objeto_imp'
            ]);
        });
    }
};
