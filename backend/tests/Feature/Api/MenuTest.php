<?php

use App\Models\Allergen;
use App\Models\MenuCategory;
use App\Models\MenuItem;

it('returns menu categories ordered with their items and allergens', function () {
    $second = MenuCategory::factory()->create(['sort_order' => 1]);
    $first = MenuCategory::factory()->create(['sort_order' => 0]);

    $allergen = Allergen::factory()->create(['name' => 'Glutine']);
    $item = MenuItem::factory()->for($first, 'category')->create();
    $item->allergens()->attach($allergen);

    $response = $this->getJson('/api/menu');

    $response->assertOk()
        ->assertJsonPath('data.0.id', $first->id)
        ->assertJsonPath('data.1.id', $second->id)
        ->assertJsonPath('data.0.menu_items.0.id', $item->id)
        ->assertJsonPath('data.0.menu_items.0.allergens.0.name', 'Glutine');
});

it('returns an empty menu when no categories exist', function () {
    $this->getJson('/api/menu')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
