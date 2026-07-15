<?php

use App\Enums\TableSessionStatus;
use App\Models\DiningTable;

it('creates a new session for a valid qr_token', function () {
    $table = DiningTable::factory()->create(['qr_token' => 'qr-tavolo-1']);

    $response = $this->postJson('/api/session', ['qr_token' => 'qr-tavolo-1']);

    $response->assertCreated()
        ->assertJsonPath('data.table_id', $table->id)
        ->assertJsonPath('data.status', TableSessionStatus::Active->value);

    expect($table->sessions()->count())->toBe(1);
});

it('reuses the active session for the same table instead of creating a new one', function () {
    $table = DiningTable::factory()->create(['qr_token' => 'qr-tavolo-2']);
    $existing = $table->sessions()->create([
        'language' => 'it',
        'status' => TableSessionStatus::Active,
        'started_at' => now(),
    ]);

    $response = $this->postJson('/api/session', ['qr_token' => 'qr-tavolo-2']);

    $response->assertOk()->assertJsonPath('data.id', $existing->id);
    expect($table->sessions()->count())->toBe(1);
});

it('returns 404 for an unknown qr_token', function () {
    $this->postJson('/api/session', ['qr_token' => 'non-esiste'])
        ->assertNotFound();
});

it('requires a qr_token', function () {
    $this->postJson('/api/session', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('qr_token');
});
