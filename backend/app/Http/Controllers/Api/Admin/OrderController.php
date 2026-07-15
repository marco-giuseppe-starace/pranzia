<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
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
            ->with('items.menuItem')
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }
}
