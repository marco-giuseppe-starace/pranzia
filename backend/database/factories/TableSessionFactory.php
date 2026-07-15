<?php

namespace Database\Factories;

use App\Enums\TableSessionStatus;
use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TableSession>
 */
class TableSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'table_id' => DiningTable::factory(),
            'language' => 'it',
            'status' => TableSessionStatus::Active,
            'started_at' => now(),
        ];
    }
}
