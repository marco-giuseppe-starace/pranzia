<?php

namespace App\Http\Resources\Admin;

use App\Enums\TableSessionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // "activeSession" e' impostata dal controller solo se esiste una
        // sessione con status Active per questo tavolo (vedi whenLoaded).
        $activeSession = $this->activeSession;

        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $activeSession ? TableSessionStatus::Active->value : TableSessionStatus::Closed->value,
            'active_session_id' => $activeSession?->id,
            // Pilota se "Chiudi tavolo" e' permesso (vedi
            // TableController::closeSession).
            'paid' => (bool) $activeSession?->paid_at,
        ];
    }
}
