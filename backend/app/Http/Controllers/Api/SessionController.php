<?php

namespace App\Http\Controllers\Api;

use App\Enums\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSessionRequest;
use App\Http\Resources\TableSessionResource;
use App\Models\DiningTable;
use Illuminate\Http\JsonResponse;

class SessionController extends Controller
{
    // Avvia (o riprende) una sessione cliente a partire dal qr_token
    // scansionato al tavolo.
    public function store(StoreSessionRequest $request): JsonResponse
    {
        $table = DiningTable::where('qr_token', $request->string('qr_token'))->first();

        if (! $table) {
            return response()->json(['message' => 'Tavolo non trovato.'], 404);
        }

        // Riusa la sessione gia' attiva per questo tavolo, se presente,
        // invece di aprirne una nuova ad ogni scansione dello stesso QR.
        $session = $table->sessions()
            ->where('status', TableSessionStatus::Active)
            ->latest('started_at')
            ->first();

        if (! $session) {
            $session = $table->sessions()->create([
                'language' => 'it',
                'status' => TableSessionStatus::Active,
                'started_at' => now(),
            ]);
        }

        return TableSessionResource::make($session)->response();
    }
}
