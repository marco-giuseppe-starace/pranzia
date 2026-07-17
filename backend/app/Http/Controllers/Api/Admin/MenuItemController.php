<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Http\Requests\UploadMenuItemImageRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MenuItemResource::collection(
            MenuItem::with('allergens')->orderBy('name')->get()
        );
    }

    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $menuItem = MenuItem::create($request->safe()->except('allergen_ids'));
        $menuItem->allergens()->sync($request->input('allergen_ids', []));

        // fresh() rilegge dal DB i valori di default (es. "available")
        // che il modello in memoria non conosce dopo la sola create().
        return MenuItemResource::make($menuItem->fresh('allergens'))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): JsonResponse
    {
        $menuItem->update($request->safe()->except('allergen_ids'));

        if ($request->has('allergen_ids')) {
            $menuItem->allergens()->sync($request->input('allergen_ids'));
        }

        return MenuItemResource::make($menuItem->load('allergens'))->response();
    }

    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $this->deleteImageFile($menuItem);
        $menuItem->delete();

        return response()->json(status: 204);
    }

    // URL relativo (non Storage::url(), che prefissa APP_URL): cosi' il
    // frontend lo risolve sempre rispetto alla propria origin, passando dal
    // proxy /storage di Vite in sviluppo (vedi vite.config.js), qualunque
    // sia l'host con cui e' stata caricata la pagina.
    public function uploadImage(UploadMenuItemImageRequest $request, MenuItem $menuItem): JsonResponse
    {
        $this->deleteImageFile($menuItem);

        $path = $request->file('image')->store('menu-items', 'public');
        $menuItem->update(['image_url' => '/storage/'.$path]);

        return MenuItemResource::make($menuItem->fresh('allergens'))->response();
    }

    private function deleteImageFile(MenuItem $menuItem): void
    {
        if ($menuItem->image_url) {
            Storage::disk('public')->delete(Str::after($menuItem->image_url, '/storage/'));
        }
    }
}
