<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_folios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('doc_type', 30)->default('OFC');
            $table->year('year');
            $table->unsignedInteger('current_number')->default(0);
            $table->timestamps();

            $table->unique(['school_id', 'doc_type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_folios');
    }
};
