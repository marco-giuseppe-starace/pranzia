<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects unauthenticated access', function () {
    $this->getJson('/api/admin/menu-categories')->assertUnauthorized();
});

it('lists categories ordered by sort_order', function () {
    Sanctum::actingAs(User::factory()->create());
    MenuCategory::factory()->create(['name' => 'Dolci', 'sort_order' => 1]);
    MenuCategory::factory()->create(['name' => 'Antipasti', 'sort_order' => 0]);

    $response = $this->getJson('/api/admin/menu-categories');

    $response->assertOk()
        ->assertJsonPath('data.0.name', 'Antipasti')
        ->assertJsonPath('data.1.name', 'Dolci');
});

it('creates a category', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/admin/menu-categories', ['name' => 'Antipasti', 'sort_order' => 0, 'group' => 'food'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Antipasti')
        ->assertJsonPath('data.group', 'food');
});

it('validates required fields when creating a category', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/admin/menu-categories', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'group']);
});

it('rejects an invalid group value', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/admin/menu-categories', ['name' => 'Antipasti', 'group' => 'not-a-group'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('group');
});

it('updates a category', function () {
    Sanctum::actingAs(User::factory()->create());
    $category = MenuCategory::factory()->create(['name' => 'Antipasti']);

    $this->putJson("/api/admin/menu-categories/{$category->id}", ['name' => 'Antipasti sfiziosi'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Antipasti sfiziosi');
});

it('deletes an empty category', function () {
    Sanctum::actingAs(User::factory()->create());
    $category = MenuCategory::factory()->create();

    $this->deleteJson("/api/admin/menu-categories/{$category->id}")->assertNoContent();

    expect(MenuCategory::find($category->id))->toBeNull();
});

it('rejects deleting a category that still has menu items', function () {
    Sanctum::actingAs(User::factory()->create());
    $category = MenuCategory::factory()->create();
    MenuItem::factory()->for($category, 'category')->create();

    $this->deleteJson("/api/admin/menu-categories/{$category->id}")
        ->assertStatus(409);

    expect(MenuCategory::find($category->id))->not->toBeNull();
});
