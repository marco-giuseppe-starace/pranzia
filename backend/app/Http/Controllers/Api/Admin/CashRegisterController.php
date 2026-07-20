<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TableSessionStatus;
use App\Exceptions\TableSessionNotActiveException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayTableSessionRequest;
use App\Http\Resources\Admin\CashRegisterTableResource;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class CashRegisterController extends Controller
{
    // Tavoli con sessione attiva non ancora incassata, con il totale
    // corrente, piu' il totale di quanto gia' incassato oggi. Un tavolo
    // gia' incassato ma non ancora chiuso (i clienti sono ancora seduti)
    // non compare piu' qui: non c'e' altro da fare finche' non se ne vanno
    // (vedi TableController::closeSession, che a quel punto lo sblocca).
    public function index(): JsonResponse
    {
        $tables = DiningTable::with('activeSession.orders')
            ->orderBy('number')
            ->get()
            ->filter(fn (DiningTable $table) => $table->activeSession && ! $table->activeSession->paid_at)
            ->values();

        return response()->json([
            'tables' => CashRegisterTableResource::collection($tables),
            'today_total' => $this->todayTotal(),
            'today_count' => $this->todayCount(),
        ]);
    }

    // Incassa il tavolo: registra solo l'orario di pagamento. La sessione
    // resta attiva (il tavolo puo' restare occupato: i clienti pagano ma
    // magari non se ne vanno subito) finche' lo staff non lo libera da
    // "Tavoli" con "Chiudi tavolo", ora permesso solo dopo l'incasso.
    public function pay(PayTableSessionRequest $request, TableSession $tableSession): JsonResponse
    {
        if ($tableSession->status !== TableSessionStatus::Active) {
            throw new TableSessionNotActiveException();
        }

        // Il cliente li inserisce gia' prima di ordinare (vedi
        // SessionController::updateGuests): qui e' solo un'eventuale
        // correzione dello staff, non piu' il valore di partenza.
        $tableSession->update([
            'paid_at' => now(),
            'guests' => $request->integer('guests', $tableSession->guests ?? 1),
        ]);

        return response()->json([
            'today_total' => $this->todayTotal(),
            'today_count' => $this->todayCount(),
        ]);
    }

    private function todayTotal(): string
    {
        return (string) Order::whereHas(
            'session',
            fn ($query) => $query->whereDate('paid_at', Date::today())
        )->sum('total');
    }

    private function todayCount(): int
    {
        return TableSession::whereDate('paid_at', Date::today())->count();
    }
}
