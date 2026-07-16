<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    // Dashboard cucina: lista ordini, filtrabile per stato
    // (?status=pending|preparing|served).
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->with('items.menuItem', 'session.table')
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    // Avanzamento stato dalla dashboard cucina (in attesa/in preparazione/servito).
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order->update(['status' => $request->enum('status', OrderStatus::class)]);

        return OrderResource::make($order->load('items.menuItem', 'session.table'))->response();
    }
}
