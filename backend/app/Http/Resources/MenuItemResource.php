<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'available' => $this->available,
            'image_url' => $this->image_url,
            // Allergeni verificati manualmente dallo staff (vedi docs/ia-guardrail.md).
            'allergens' => AllergenResource::collection($this->whenLoaded('allergens')),
        ];
    }
}
