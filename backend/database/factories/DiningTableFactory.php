<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DiningTable>
 */
class DiningTableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'qr_token' => fake()->unique()->uuid(),
            'number' => fake()->unique()->numberBetween(1, 100),
        ];
    }
}
