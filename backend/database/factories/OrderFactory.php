<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => TableSession::factory(),
            'status' => OrderStatus::Pending,
            'total' => fake()->randomFloat(2, 5, 50),
        ];
    }
}
