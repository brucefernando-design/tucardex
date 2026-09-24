<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admissions')) {
            Schema::create('admissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('folio', 30)->unique();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('curp', 18)->nullable();
                $table->date('birth_date')->nullable();
                $table->enum('gender', ['M', 'F'])->nullable();
                $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
                $table->string('grade_level', 100)->nullable();
                $table->string('previous_school', 150)->nullable();
                
                $table->string('guardian_name', 150);
                $table->string('guardian_relationship', 50)->default('Tutor');
                $table->string('guardian_phone', 30);
                $table->string('guardian_email', 120);
                $table->text('address')->nullable();
                
                $table->string('birth_certificate_path')->nullable();
                $table->string('curp_path')->nullable();
                $table->string('address_proof_path')->nullable();
                $table->string('previous_grades_path')->nullable();
                $table->text('medical_notes')->nullable();
                
                $table->enum('status', ['pendiente', 'en_revision', 'aceptada', 'rechazada', 'matriculada'])->default('pendiente');
                $table->text('internal_notes')->nullable();
                $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
