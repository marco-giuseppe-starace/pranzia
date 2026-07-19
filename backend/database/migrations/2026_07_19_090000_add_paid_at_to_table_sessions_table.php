<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            // Valorizzato quando lo staff incassa il tavolo dalla cassa:
            // la sessione viene chiusa contestualmente (vedi
            // CashRegisterController::pay), niente stato "paid" separato.
            $table->timestamp('paid_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};
