<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'spei_enabled')) {
                $table->boolean('spei_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'spei_bank')) {
                $table->string('spei_bank', 60)->nullable();
            }
            if (!Schema::hasColumn('settings', 'spei_clabe')) {
                $table->string('spei_clabe', 18)->nullable();
            }
            if (!Schema::hasColumn('settings', 'spei_beneficiary')) {
                $table->string('spei_beneficiary', 150)->nullable();
            }
            if (!Schema::hasColumn('settings', 'spei_instructions')) {
                $table->text('spei_instructions')->nullable();
            }
            if (!Schema::hasColumn('settings', 'mercadopago_enabled')) {
                $table->boolean('mercadopago_enabled')->default(false);
            }
            if (!Schema::hasColumn('settings', 'mercadopago_public_key')) {
                $table->string('mercadopago_public_key', 255)->nullable();
            }
            if (!Schema::hasColumn('settings', 'mercadopago_access_token')) {
                $table->text('mercadopago_access_token')->nullable();
            }
            if (!Schema::hasColumn('settings', 'stripe_enabled')) {
                $table->boolean('stripe_enabled')->default(false);
            }
            if (!Schema::hasColumn('settings', 'stripe_public_key')) {
                $table->string('stripe_public_key', 255)->nullable();
            }
            if (!Schema::hasColumn('settings', 'stripe_secret_key')) {
                $table->text('stripe_secret_key')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'spei_enabled', 'spei_bank', 'spei_clabe', 'spei_beneficiary', 'spei_instructions',
                'mercadopago_enabled', 'mercadopago_public_key', 'mercadopago_access_token',
                'stripe_enabled', 'stripe_public_key', 'stripe_secret_key'
            ]);
        });
    }
};
