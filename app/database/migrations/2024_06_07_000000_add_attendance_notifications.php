<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'attendance_whatsapp_enabled')) {
                    $table->boolean('attendance_whatsapp_enabled')->default(true)->after('whatsapp_enabled');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'notified_at')) {
                    $table->timestamp('notified_at')->nullable()->after('remarks');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (Schema::hasColumn('settings', 'attendance_whatsapp_enabled')) {
                    $table->dropColumn('attendance_whatsapp_enabled');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'notified_at')) {
                    $table->dropColumn('notified_at');
                }
            });
        }
    }
};
