<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'whatsapp_enabled')) {
                $table->boolean('whatsapp_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'email_reminders_enabled')) {
                $table->boolean('email_reminders_enabled')->default(true);
            }
            if (!Schema::hasColumn('settings', 'reminder_days_before')) {
                $table->integer('reminder_days_before')->default(3);
            }
            if (!Schema::hasColumn('settings', 'reminder_days_after')) {
                $table->integer('reminder_days_after')->default(3);
            }
            if (!Schema::hasColumn('settings', 'reminder_hour')) {
                $table->string('reminder_hour', 5)->default('08:30');
            }
            if (!Schema::hasColumn('settings', 'reminder_min_delay')) {
                $table->integer('reminder_min_delay')->default(45); // 45 segundos
            }
            if (!Schema::hasColumn('settings', 'reminder_max_delay')) {
                $table->integer('reminder_max_delay')->default(118); // 118 segundos (1m 58s)
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable();
            }
            if (!Schema::hasColumn('payments', 'reminder_count')) {
                $table->integer('reminder_count')->default(0);
            }
            if (!Schema::hasColumn('payments', 'last_reminder_channel')) {
                $table->string('last_reminder_channel', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_enabled',
                'email_reminders_enabled',
                'reminder_days_before',
                'reminder_days_after',
                'reminder_hour',
                'reminder_min_delay',
                'reminder_max_delay',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_sent_at',
                'reminder_count',
                'last_reminder_channel',
            ]);
        });
    }
};
