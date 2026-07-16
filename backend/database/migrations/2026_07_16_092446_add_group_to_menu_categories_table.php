<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            // Macro-sezione verticale del menu (cibo/bevande/dolci): default
            // 'food' per non rompere le categorie gia' esistenti, poi
            // corretta dal seeder/dall'admin per bevande e dolci.
            $table->string('group')->default('food')->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
