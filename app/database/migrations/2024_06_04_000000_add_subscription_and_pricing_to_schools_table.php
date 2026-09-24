<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->decimal('price_per_student', 8, 2)->nullable()->default(null)->after('plan');
            $table->decimal('minimum_monthly_fee', 8, 2)->nullable()->default(null)->after('price_per_student');
            $table->boolean('is_public_school')->default(false)->after('minimum_monthly_fee');
            $table->boolean('canvas_enabled')->default(false)->after('is_public_school');
            $table->boolean('api_enabled')->default(false)->after('canvas_enabled');
            $table->string('subscription_status', 30)->default('active')->after('status');
            $table->date('billing_renews_at')->nullable()->after('subscription_status');
            $table->json('features_override')->nullable()->after('billing_renews_at');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'price_per_student',
                'minimum_monthly_fee',
                'is_public_school',
                'canvas_enabled',
                'api_enabled',
                'subscription_status',
                'billing_renews_at',
                'features_override',
            ]);
        });
    }
};
