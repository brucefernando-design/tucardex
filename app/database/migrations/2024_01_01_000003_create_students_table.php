<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique();        // Matrícula / RUDE
            $table->string('first_name');
            $table->string('last_name');
            $table->string('dni')->nullable()->unique();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'Otro'])->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('guardian_name')->nullable();   // Apoderado
            $table->string('guardian_phone')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->date('enrollment_date')->nullable();
            $table->enum('status', ['activo', 'inactivo', 'retirado'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
