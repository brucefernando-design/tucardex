<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payments', 'token')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('token', 64)->nullable()->unique()->after('id');
            });
        }

        // Backfill existing payments with unique cryptographically random tokens
        $payments = \App\Models\Payment::withoutGlobalScopes()->whereNull('token')->get();
        foreach ($payments as $payment) {
            $payment->token = Str::random(40);
            $payment->saveQuietly();
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'token')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('token');
            });
        }
    }
};
