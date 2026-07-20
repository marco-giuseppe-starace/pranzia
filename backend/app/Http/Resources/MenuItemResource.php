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
            // Serve al frontend per capire se e' una bevanda (niente
            // suggerimento "senza [ingrediente]" nella nota, non ha senso
            // per un piatto senza ingredienti elencati come "acqua 0,5L").
            'group' => $this->whenLoaded('category', fn () => $this->category->group),
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'available' => $this->available,
            'image_url' => $this->image_url,
            // Pilota se mostrare "Ripristina originale" in admin: non
            // esponiamo il path di storage vero e proprio, solo se esiste.
            'has_original_image' => (bool) $this->original_image_path,
            // Allergeni verificati manualmente dallo staff (vedi docs/ia-guardrail.md).
            'allergens' => AllergenResource::collection($this->whenLoaded('allergens')),
        ];
    }
}
