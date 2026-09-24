<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('platform_fee_enabled')->default(false)->after('tuition_amount');
            $table->decimal('platform_fee_amount', 10, 2)->default(30.00)->after('platform_fee_enabled');
            $table->string('platform_fee_label')->default('Plataforma digital / portal familias')->after('platform_fee_amount');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['platform_fee_enabled', 'platform_fee_amount', 'platform_fee_label']);
        });
    }
};
