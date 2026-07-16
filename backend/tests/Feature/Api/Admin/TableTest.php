<?php

use App\Enums\TableSessionStatus;
use App\Models\DiningTable;
use App\Models\TableSession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects unauthenticated access', function () {
    $this->getJson('/api/admin/tables')->assertUnauthorized();
});

it('lists tables with their occupied/free status', function () {
    Sanctum::actingAs(User::factory()->create());

    $free = DiningTable::factory()->create(['number' => 1]);
    $occupied = DiningTable::factory()->create(['number' => 2]);
    TableSession::factory()->create(['table_id' => $occupied->id, 'status' => TableSessionStatus::Active]);

    $response = $this->getJson('/api/admin/tables')->assertOk();

    $response->assertJsonFragment(['number' => 1, 'status' => 'closed'])
        ->assertJsonFragment(['number' => 2, 'status' => 'active']);
});

it('closes the active session of a table', function () {
    Sanctum::actingAs(User::factory()->create());

    $table = DiningTable::factory()->create();
    $session = TableSession::factory()->create(['table_id' => $table->id, 'status' => TableSessionStatus::Active]);

    $this->postJson("/api/admin/tables/{$table->id}/close-session")
        ->assertOk()
        ->assertJsonPath('data.status', 'closed')
        ->assertJsonPath('data.active_session_id', null);

    expect($session->fresh()->status)->toBe(TableSessionStatus::Closed);
});

it('does nothing when closing a table with no active session', function () {
    Sanctum::actingAs(User::factory()->create());
    $table = DiningTable::factory()->create();

    $this->postJson("/api/admin/tables/{$table->id}/close-session")
        ->assertOk()
        ->assertJsonPath('data.status', 'closed');
});

it('lets a new session start after the previous one is closed', function () {
    $table = DiningTable::factory()->create(['qr_token' => 'qr-riuso']);
    $old = TableSession::factory()->create(['table_id' => $table->id, 'status' => TableSessionStatus::Active]);

    Sanctum::actingAs(User::factory()->create());
    $this->postJson("/api/admin/tables/{$table->id}/close-session")->assertOk();

    $response = $this->postJson('/api/session', ['qr_token' => 'qr-riuso']);

    $response->assertCreated()->assertJsonPath('data.id', function ($id) use ($old) {
        return $id !== $old->id;
    });
});
