<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CashRegisterTableResource;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class CashRegisterController extends Controller
{
    // Tavoli ancora da incassare (sessione attiva) con il totale corrente,
    // piu' il totale di quanto gia' incassato oggi.
    public function index(): JsonResponse
    {
        $tables = DiningTable::with('activeSession.orders')
            ->orderBy('number')
            ->get()
            ->filter(fn (DiningTable $table) => $table->activeSession)
            ->values();

        return response()->json([
            'tables' => CashRegisterTableResource::collection($tables),
            'today_total' => $this->todayTotal(),
            'today_count' => $this->todayCount(),
        ]);
    }

    // Incassa il tavolo: registra l'orario di pagamento e chiude la
    // sessione, cosi' la prossima scansione dello stesso QR ne apre una
    // nuova invece di riusare quella - ormai pagata - del cliente
    // precedente (stessa logica di TableController::closeSession).
    public function pay(TableSession $tableSession): JsonResponse
    {
        if ($tableSession->status !== TableSessionStatus::Active) {
            return response()->json(['message' => 'Questa sessione non e\' piu\' attiva.'], 422);
        }

        $tableSession->update([
            'status' => TableSessionStatus::Closed,
            'paid_at' => now(),
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
