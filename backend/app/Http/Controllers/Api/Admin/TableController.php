<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TableSessionStatus;
use App\Exceptions\TableSessionNotPaidException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TableResource;
use App\Models\DiningTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TableController extends Controller
{
    // Dashboard admin: stato di ogni tavolo (libero/occupato), per sapere
    // quali sessioni chiudere quando i clienti se ne vanno.
    public function index(): AnonymousResourceCollection
    {
        $tables = DiningTable::with('activeSession')->orderBy('number')->get();

        return TableResource::collection($tables);
    }

    // Chiude (libera) il tavolo quando i clienti se ne vanno fisicamente:
    // richiede che la sessione sia gia' stata incassata da "In cassa",
    // altrimenti lo staff rischierebbe di liberare un tavolo non pagato.
    public function closeSession(DiningTable $table): JsonResponse
    {
        $session = $table->activeSession;

        if ($session && ! $session->paid_at) {
            throw new TableSessionNotPaidException();
        }

        $session?->update(['status' => TableSessionStatus::Closed]);

        return TableResource::make($table->fresh('activeSession'))->response();
    }
}
