<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects unauthenticated access', function () {
    $this->getJson('/api/admin/orders')->assertUnauthorized();
});

it('lists all orders for the kitchen dashboard', function () {
    Sanctum::actingAs(User::factory()->create());
    Order::factory()->count(2)->create();

    $this->getJson('/api/admin/orders')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('filters orders by status', function () {
    Sanctum::actingAs(User::factory()->create());
    Order::factory()->create(['status' => OrderStatus::Pending]);
    Order::factory()->create(['status' => OrderStatus::Served]);

    $this->getJson('/api/admin/orders?status=served')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', OrderStatus::Served->value);
});

it('rejects unauthenticated status updates', function () {
    $order = Order::factory()->create();

    $this->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'preparing'])
        ->assertUnauthorized();
});

it('advances an order status from the kitchen dashboard', function () {
    Sanctum::actingAs(User::factory()->create());
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    $this->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'preparing'])
        ->assertOk()
        ->assertJsonPath('data.status', OrderStatus::Preparing->value);

    expect($order->fresh()->status)->toBe(OrderStatus::Preparing);
});

it('rejects an invalid status value', function () {
    Sanctum::actingAs(User::factory()->create());
    $order = Order::factory()->create();

    $this->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'not-a-status'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});
