<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            // Null = il cliente non li ha ancora inseriti (mostra il modal
            // bloccante). Il default a 1 non permetteva di distinguere "non
            // ancora impostato" da "il cliente ha confermato che e' solo".
            $table->unsignedInteger('guests')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            $table->unsignedInteger('guests')->default(1)->change();
        });
    }
};
