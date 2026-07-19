<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->table_id,
            'table_number' => $this->table->number,
            'language' => $this->language,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'paid' => (bool) $this->paid_at,
        ];
    }
}
