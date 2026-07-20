<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ServiceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceRequestController extends Controller
{
    // Dashboard staff: richieste in attesa (piu' recente per prima), per
    // sapere subito a quale tavolo portare cosa.
    public function index(): AnonymousResourceCollection
    {
        $requests = ServiceRequest::where('status', ServiceRequestStatus::Pending->value)
            ->with('session.table')
            ->latest()
            ->get();

        return ServiceRequestResource::collection($requests);
    }

    public function resolve(ServiceRequest $serviceRequest): JsonResponse
    {
        $serviceRequest->update([
            'status' => ServiceRequestStatus::Done,
            'resolved_at' => now(),
        ]);

        return ServiceRequestResource::make($serviceRequest->load('session.table'))->response();
    }
}
