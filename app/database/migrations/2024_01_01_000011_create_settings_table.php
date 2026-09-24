<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('Colegio SaaS');
            $table->string('academic_year')->default('2026');
            $table->enum('active_period', ['1er Trimestre', '2do Trimestre', '3er Trimestre', 'Final'])->default('1er Trimestre');
            $table->string('currency')->default('Bs');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('director')->nullable();
            $table->decimal('tuition_amount', 10, 2)->default(250);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
