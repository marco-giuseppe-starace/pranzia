<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            // Numero di persone al tavolo, per calcolare il totale coperti
            // nello scontrino (settings.cover_charge x guests). Impostato
            // dallo staff al momento dell'incasso (vedi
            // CashRegisterController::pay), non dal cliente.
            $table->unsignedInteger('guests')->default(1)->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropColumn('guests');
        });
    }
};
