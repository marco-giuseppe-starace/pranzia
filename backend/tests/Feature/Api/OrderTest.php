<?php

use App\Enums\TableSessionStatus;
use App\Models\DiningTable;
use App\Models\MenuItem;
use App\Models\TableSession;

it('creates an order with total computed from current menu prices', function () {
    $session = TableSession::factory()->create();
    $item = MenuItem::factory()->create(['price' => 6.50]);

    $response = $this->postJson('/api/orders', [
        'session_id' => $session->id,
        'items' => [
            ['menu_item_id' => $item->id, 'quantity' => 2, 'notes' => 'senza sale'],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.total', '13.00')
        ->assertJsonPath('data.items.0.price_at_order', '6.50')
        ->assertJsonPath('data.items.0.notes', 'senza sale');
});

it('includes the table number so the customer sees which table the order belongs to', function () {
    $table = DiningTable::factory()->create(['number' => 12]);
    $session = TableSession::factory()->create(['table_id' => $table->id]);
    $item = MenuItem::factory()->create();

    $response = $this->postJson('/api/orders', [
        'session_id' => $session->id,
        'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
    ]);

    $response->assertCreated()->assertJsonPath('data.table_number', 12);

    $this->getJson("/api/orders/{$session->id}")
        ->assertOk()
        ->assertJsonPath('data.0.table_number', 12);
});

it('ignores a client-supplied price and always uses the current menu price', function () {
    $session = TableSession::factory()->create();
    $item = MenuItem::factory()->create(['price' => 10.00]);

    $response = $this->postJson('/api/orders', [
        'session_id' => $session->id,
        'items' => [
            ['menu_item_id' => $item->id, 'quantity' => 1, 'price_at_order' => 0.01],
        ],
    ]);

    $response->assertCreated()->assertJsonPath('data.items.0.price_at_order', '10.00');
});

it('rejects an order for a menu item marked unavailable', function () {
    $session = TableSession::factory()->create();
    $item = MenuItem::factory()->create(['available' => false]);

    $this->postJson('/api/orders', [
        'session_id' => $session->id,
        'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
    ])->assertUnprocessable();
});

it('rejects an order for a closed session', function () {
    $session = TableSession::factory()->create(['status' => TableSessionStatus::Closed]);
    $item = MenuItem::factory()->create();

    $this->postJson('/api/orders', [
        'session_id' => $session->id,
        'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
    ])->assertUnprocessable();
});

it('validates that items is a non-empty array', function () {
    $session = TableSession::factory()->create();

    $this->postJson('/api/orders', ['session_id' => $session->id, 'items' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items');
});

it('lists orders for a session', function () {
    $session = TableSession::factory()->create();
    $item = MenuItem::factory()->create();

    $this->postJson('/api/orders', [
        'session_id' => $session->id,
        'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
    ])->assertCreated();

    $this->getJson("/api/orders/{$session->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
