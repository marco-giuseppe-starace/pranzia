<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('table_sessions')->onDelete('cascade');
            // Tipo predefinito (acqua, bicchiere, sale, ...) o "other" con
            // nota libera: vedi App\Enums\ServiceRequestType.
            $table->string('type');
            $table->string('note')->nullable();
            // Cast a App\Enums\ServiceRequestStatus lato modello Eloquent.
            $table->string('status')->default('pending');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
