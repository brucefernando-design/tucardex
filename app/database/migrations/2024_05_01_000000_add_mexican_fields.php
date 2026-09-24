<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'curp')) {
                $table->string('curp', 18)->nullable()->after('dni');
            }
        });

        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'cct')) {
                $table->string('cct', 20)->nullable()->after('name');
            }
            if (!Schema::hasColumn('schools', 'rvoe')) {
                $table->string('rvoe', 60)->nullable()->after('cct');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'cct')) {
                $table->string('cct', 20)->nullable()->after('school_name');
            }
            if (!Schema::hasColumn('settings', 'rvoe')) {
                $table->string('rvoe', 60)->nullable()->after('cct');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('curp');
        });
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['cct', 'rvoe']);
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['cct', 'rvoe']);
        });
    }
};
