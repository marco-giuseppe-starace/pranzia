<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Il file caricato dall'admin, mai toccato da rifilo/ridimensionamento/
            // ritaglio: permette di "Ripristina originale" dopo un ritaglio,
            // rigenerando image_url da qui invece di perdere per sempre la foto
            // di partenza.
            $table->string('original_image_path')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('original_image_path');
        });
    }
};
