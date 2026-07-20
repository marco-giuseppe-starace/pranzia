<?php

namespace App\Http\Controllers\Api;

use App\Enums\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSessionRequest;
use App\Http\Requests\UpdateSessionGuestsRequest;
use App\Http\Resources\TableSessionResource;
use App\Models\DiningTable;
use App\Models\TableSession;
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

        // Evita una query extra per il numero del tavolo nella resource:
        // $table e' gia' in memoria, la relazione non va ricaricata dal DB.
        $session->setRelation('table', $table);

        return TableSessionResource::make($session)->response();
    }

    // Interrogato dal frontend cliente per sapere quando lo staff ha
    // incassato il tavolo o inviato un'anteprima del conto (in entrambi i
    // casi mostra "Ricevuta") e se i coperti sono gia' stati inseriti
    // (guests null = non ancora, mostra il modal bloccante prima di poter
    // ordinare).
    public function status(TableSession $tableSession): JsonResponse
    {
        return response()->json([
            'paid' => (bool) $tableSession->paid_at,
            'receipt_sent' => (bool) $tableSession->receipt_sent_at,
            'guests' => $tableSession->guests,
        ]);
    }

    // Il cliente inserisce (o corregge) quante persone sono al tavolo,
    // prima ancora di ordinare. Lo staff puo' comunque correggerlo al
    // momento dell'incasso (vedi CashRegisterController::pay).
    public function updateGuests(UpdateSessionGuestsRequest $request, TableSession $tableSession): JsonResponse
    {
        if ($tableSession->status !== TableSessionStatus::Active) {
            return response()->json(['message' => 'La sessione non e\' attiva.'], 422);
        }

        $tableSession->update(['guests' => $request->integer('guests')]);

        return response()->json(['guests' => $tableSession->guests]);
    }
}
