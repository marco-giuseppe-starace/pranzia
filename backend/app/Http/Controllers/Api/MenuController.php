<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuCategoryResource;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    // Il parametro "lang" e' accettato per compatibilita' con le specifiche
    // ma non ha ancora effetto: il menu resta solo in italiano. La
    // traduzione avviene tramite POST /api/ai/ask (Milestone 4), non qui.
    public function index(Request $request): AnonymousResourceCollection
    {
        $excludedAllergenIds = collect(explode(',', (string) $request->query('exclude_allergens', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->all();

        $categories = MenuCategory::with('menuItems.allergens')
            ->orderBy('sort_order')
            ->get();

        if (! empty($excludedAllergenIds)) {
            // Filtro basato solo su dati verificati dallo staff
            // (menu_item_allergens), nessuna chiamata IA coinvolta.
            $categories->each(function (MenuCategory $category) use ($excludedAllergenIds) {
                $category->setRelation(
                    'menuItems',
                    $category->menuItems
                        ->reject(fn ($item) => $item->allergens->pluck('id')->intersect($excludedAllergenIds)->isNotEmpty())
                        ->values()
                );
            });
        }

        return MenuCategoryResource::collection($categories);
    }
}
