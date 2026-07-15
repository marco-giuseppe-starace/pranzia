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
    // ma non ha ancora effetto: la traduzione automatica del menu arriva
    // nella Milestone 4 (integrazione IA). Per ora il menu e' solo in italiano.
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = MenuCategory::with('menuItems.allergens')
            ->orderBy('sort_order')
            ->get();

        return MenuCategoryResource::collection($categories);
    }
}
