<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('invoice_number')->nullable()->unique();
            $table->string('concept');               // Ej: "Pensión Marzo", "Matrícula"
            $table->decimal('amount', 10, 2);
            $table->string('period')->nullable();    // Mes / gestión
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->enum('method', ['efectivo', 'transferencia', 'tarjeta', 'qr'])->nullable();
            $table->enum('status', ['pendiente', 'pagado', 'vencido', 'anulado'])->default('pendiente');
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
