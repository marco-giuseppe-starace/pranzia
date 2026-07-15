<?php

use App\Models\Allergen;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects unauthenticated access', function () {
    $this->getJson('/api/admin/allergens')->assertUnauthorized();
});

it('lists allergens for an authenticated admin', function () {
    Sanctum::actingAs(User::factory()->create());
    Allergen::factory()->count(3)->create();

    $this->getJson('/api/admin/allergens')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('creates an allergen', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/admin/allergens', ['name' => 'Sesamo'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Sesamo');
});

it('rejects a duplicate allergen name', function () {
    Sanctum::actingAs(User::factory()->create());
    Allergen::factory()->create(['name' => 'Sesamo']);

    $this->postJson('/api/admin/allergens', ['name' => 'Sesamo'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('updates an allergen', function () {
    Sanctum::actingAs(User::factory()->create());
    $allergen = Allergen::factory()->create(['name' => 'Glutine']);

    $this->putJson("/api/admin/allergens/{$allergen->id}", ['name' => 'Glutine (cereali)'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Glutine (cereali)');
});

it('deletes an allergen', function () {
    Sanctum::actingAs(User::factory()->create());
    $allergen = Allergen::factory()->create();

    $this->deleteJson("/api/admin/allergens/{$allergen->id}")->assertNoContent();

    expect(Allergen::find($allergen->id))->toBeNull();
});
