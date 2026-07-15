<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuCategoryRequest;
use App\Http\Requests\UpdateMenuCategoryRequest;
use App\Http\Resources\MenuCategoryResource;
use App\Models\MenuCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MenuCategoryResource::collection(MenuCategory::orderBy('sort_order')->get());
    }

    public function store(StoreMenuCategoryRequest $request): JsonResponse
    {
        $category = MenuCategory::create($request->validated());

        return MenuCategoryResource::make($category)->response()->setStatusCode(201);
    }

    public function update(UpdateMenuCategoryRequest $request, MenuCategory $menuCategory): JsonResponse
    {
        $menuCategory->update($request->validated());

        return MenuCategoryResource::make($menuCategory)->response();
    }

    public function destroy(MenuCategory $menuCategory): JsonResponse
    {
        try {
            $menuCategory->delete();
        } catch (QueryException) {
            // menu_items.category_id ha onDelete('restrict'): non si elimina
            // una categoria che contiene ancora piatti.
            return response()->json([
                'message' => 'Non e\' possibile eliminare una categoria che contiene ancora piatti.',
            ], 409);
        }

        return response()->json(status: 204);
    }
}
