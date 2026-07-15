<?php

use App\Models\Allergen;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects unauthenticated access', function () {
    $this->getJson('/api/admin/menu-items')->assertUnauthorized();
});

it('lists menu items for an authenticated admin', function () {
    Sanctum::actingAs(User::factory()->create());
    MenuItem::factory()->count(2)->create();

    $this->getJson('/api/admin/menu-items')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('creates a menu item with allergens', function () {
    Sanctum::actingAs(User::factory()->create());
    $category = MenuCategory::factory()->create();
    $allergen = Allergen::factory()->create();

    $response = $this->postJson('/api/admin/menu-items', [
        'category_id' => $category->id,
        'name' => 'Tiramisu',
        'price' => 6.00,
        'allergen_ids' => [$allergen->id],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Tiramisu')
        ->assertJsonPath('data.available', true)
        ->assertJsonPath('data.allergens.0.id', $allergen->id);
});

it('validates required fields when creating a menu item', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/admin/menu-items', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id', 'name', 'price']);
});

it('updates a menu item', function () {
    Sanctum::actingAs(User::factory()->create());
    $menuItem = MenuItem::factory()->create(['price' => 5.00]);

    $this->putJson("/api/admin/menu-items/{$menuItem->id}", ['price' => 7.50])
        ->assertOk()
        ->assertJsonPath('data.price', '7.50');
});

it('deletes a menu item', function () {
    Sanctum::actingAs(User::factory()->create());
    $menuItem = MenuItem::factory()->create();

    $this->deleteJson("/api/admin/menu-items/{$menuItem->id}")->assertNoContent();

    expect(MenuItem::find($menuItem->id))->toBeNull();
});
