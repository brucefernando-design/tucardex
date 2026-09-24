<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electronic_billing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('electronic_billing_settings', 'private_key_path')) {
                $table->string('private_key_path')->nullable()->after('certificate_path');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'csd_status')) {
                $table->string('csd_status')->default('pendiente')->after('certificate_password');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'csd_numero_serie')) {
                $table->string('csd_numero_serie')->nullable()->after('csd_status');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'csd_valido_hasta')) {
                $table->timestamp('csd_valido_hasta')->nullable()->after('csd_numero_serie');
            }
            if (!Schema::hasColumn('electronic_billing_settings', 'csd_error')) {
                $table->text('csd_error')->nullable()->after('csd_valido_hasta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('electronic_billing_settings', function (Blueprint $table) {
            $table->dropColumn([
                'private_key_path',
                'csd_status',
                'csd_numero_serie',
                'csd_valido_hasta',
                'csd_error',
            ]);
        });
    }
};
