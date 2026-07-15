<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('table_sessions')->onDelete('cascade');
            // Cast a App\Enums\AiInteractionType lato modello Eloquent.
            $table->string('type');
            $table->unsignedInteger('tokens_input');
            $table->unsignedInteger('tokens_output');
            // Stima di costo in euro, per il report spesa mensile IA.
            $table->decimal('cost_estimate', 8, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
    }
};
