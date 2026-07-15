<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAllergenRequest;
use App\Http\Requests\UpdateAllergenRequest;
use App\Http\Resources\AllergenResource;
use App\Models\Allergen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AllergenController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AllergenResource::collection(Allergen::orderBy('name')->get());
    }

    public function store(StoreAllergenRequest $request): JsonResponse
    {
        $allergen = Allergen::create($request->validated());

        return AllergenResource::make($allergen)->response()->setStatusCode(201);
    }

    public function update(UpdateAllergenRequest $request, Allergen $allergen): JsonResponse
    {
        $allergen->update($request->validated());

        return AllergenResource::make($allergen)->response();
    }

    public function destroy(Allergen $allergen): JsonResponse
    {
        $allergen->delete();

        return response()->json(status: 204);
    }
}
