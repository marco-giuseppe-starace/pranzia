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
