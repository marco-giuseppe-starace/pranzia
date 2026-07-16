<?php

namespace Database\Factories;

use App\Enums\MenuCategoryGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\MenuCategory>
 */
class MenuCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'sort_order' => fake()->numberBetween(0, 10),
            'group' => MenuCategoryGroup::Food,
        ];
    }
}
