<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('relationship')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'student_id']);
            $table->index('school_id');
        });

        // Actualizar nombre visible del rol estudiante (quitar "/ Padre")
        Role::where('slug', 'estudiante')->update(['name' => 'Estudiante']);

        // Crear rol padre si no existe
        Role::firstOrCreate(
            ['slug' => 'padre'],
            [
                'name' => 'Padre / Tutor',
                'description' => 'Consulta de expediente de hijos',
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};
