<?php

namespace App\Http\Controllers\Api;

use App\Enums\ServiceRequestType;
use App\Enums\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceRequestController extends Controller
{
    public function store(StoreServiceRequestRequest $request): JsonResponse
    {
        $session = TableSession::findOrFail($request->integer('session_id'));

        if ($session->status !== TableSessionStatus::Active) {
            return response()->json(['message' => 'La sessione non e\' attiva.'], 422);
        }

        $serviceRequest = ServiceRequest::create([
            'session_id' => $session->id,
            'type' => $request->enum('type', ServiceRequestType::class),
            'note' => $request->input('note'),
        ]);

        // fresh() rilegge dal DB il valore di default di "status" (il
        // modello in memoria non lo conosce dopo la sola create()).
        return ServiceRequestResource::make($serviceRequest->fresh('session.table'))
            ->response()
            ->setStatusCode(201);
    }

    // Le proprie richieste (stesso tavolo/sessione): il cliente vede se
    // sono ancora in attesa o gia' state gestite dallo staff.
    public function index(int $sessionId): AnonymousResourceCollection
    {
        $requests = ServiceRequest::where('session_id', $sessionId)
            ->with('session.table')
            ->latest()
            ->get();

        return ServiceRequestResource::collection($requests);
    }
}
