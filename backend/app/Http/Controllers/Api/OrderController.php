<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $session = TableSession::findOrFail($request->integer('session_id'));

        if ($session->status !== TableSessionStatus::Active) {
            return response()->json(['message' => 'La sessione non e\' attiva.'], 422);
        }

        $menuItems = MenuItem::whereIn('id', collect($request->input('items'))->pluck('menu_item_id'))
            ->get()
            ->keyBy('id');

        foreach ($request->input('items') as $item) {
            $menuItem = $menuItems->get($item['menu_item_id']);
            if (! $menuItem->available) {
                return response()->json([
                    'message' => "Il piatto \"{$menuItem->name}\" non e' al momento disponibile.",
                ], 422);
            }
        }

        // Il prezzo va sempre letto dal menu attuale, mai da un valore
        // passato dal client, per evitare manomissioni lato frontend.
        $order = DB::transaction(function () use ($session, $request, $menuItems) {
            $order = $session->orders()->create([
                'status' => OrderStatus::Pending,
                'total' => 0,
            ]);

            $total = 0;
            foreach ($request->input('items') as $item) {
                $menuItem = $menuItems->get($item['menu_item_id']);
                $priceAtOrder = $menuItem->price;
                $total += $priceAtOrder * $item['quantity'];

                $order->items()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                    'price_at_order' => $priceAtOrder,
                ]);
            }

            $order->update(['total' => $total]);

            return $order;
        });

        return OrderResource::make($order->load('items.menuItem'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $sessionId): AnonymousResourceCollection
    {
        $orders = Order::where('session_id', $sessionId)
            ->with('items.menuItem')
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }
}
