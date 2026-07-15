<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            // Restrict: uno storico ordini non deve perdere il riferimento
            // al piatto anche se il piatto viene rimosso dal menu attuale.
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('restrict');
            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();
            // Prezzo al momento dell'ordine: non deve cambiare se il prezzo
            // del piatto viene aggiornato successivamente nel menu.
            $table->decimal('price_at_order', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
