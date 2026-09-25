<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('token', 48)->unique();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('doc_type', 50);
            $table->string('doc_title', 140);
            $table->string('folio', 60)->nullable();
            $table->string('student_name', 160);
            $table->string('student_code', 60)->nullable();
            $table->string('student_curp', 40)->nullable();
            $table->string('course_name', 120)->nullable();
            $table->string('level', 60)->nullable();
            $table->string('academic_year', 30)->nullable();
            $table->string('school_name', 160);
            $table->string('school_cct', 40)->nullable();
            $table->string('director_name', 140)->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'doc_type', 'academic_year'], 'doc_verif_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
