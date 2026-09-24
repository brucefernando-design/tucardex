<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas que pertenecen a un colegio (tenant).
     */
    private array $tenantTables = [
        'users', 'students', 'teachers', 'courses', 'subjects', 'enrollments',
        'attendances', 'grades', 'schedules', 'payments', 'announcements',
        'settings', 'events', 'assignments', 'books', 'loans', 'incidents', 'messages',
    ];

    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('plan', ['basico', 'pro', 'institucional'])->default('basico');
            $table->enum('status', ['activo', 'suspendido'])->default('activo');
            $table->date('trial_ends_at')->nullable();
            $table->timestamps();
        });

        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'school_id')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('school_id')->nullable()->after('id')
                    ->constrained('schools')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tenantTables as $table) {
            if (Schema::hasColumn($table, 'school_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('school_id');
                });
            }
        }

        Schema::dropIfExists('schools');
    }
};
