<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Ej: "1ro de Secundaria"
            $table->enum('level', ['Inicial', 'Primaria', 'Secundaria'])->default('Secundaria');
            $table->string('grade')->nullable();     // Ej: "1ro", "2do"
            $table->string('section')->default('A'); // Paralelo
            $table->enum('shift', ['Mañana', 'Tarde', 'Noche'])->default('Mañana');
            $table->unsignedSmallInteger('capacity')->default(35);
            $table->foreignId('tutor_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('academic_year')->default('2026');
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
