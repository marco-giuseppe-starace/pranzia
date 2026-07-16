<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TableSessionStatus;
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

    // Chiude la sessione attiva del tavolo (se presente), cosi' la
    // prossima scansione dello stesso QR apre una sessione nuova invece
    // di riusare quella - ormai conclusa - del cliente precedente.
    public function closeSession(DiningTable $table): JsonResponse
    {
        $table->activeSession?->update(['status' => TableSessionStatus::Closed]);

        return TableResource::make($table->fresh('activeSession'))->response();
    }
}
