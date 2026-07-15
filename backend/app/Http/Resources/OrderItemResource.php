<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_item_id' => $this->menu_item_id,
            'menu_item_name' => $this->whenLoaded('menuItem', fn () => $this->menuItem->name),
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'price_at_order' => $this->price_at_order,
        ];
    }
}
