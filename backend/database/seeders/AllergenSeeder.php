<?php

namespace Database\Seeders;

use App\Models\Allergen;
use Illuminate\Database\Seeder;

class AllergenSeeder extends Seeder
{
    // I 14 allergeni UE standard (Regolamento UE 1169/2011), in italiano.
    private const ALLERGENS = [
        'Glutine',
        'Crostacei',
        'Uova',
        'Pesce',
        'Arachidi',
        'Soia',
        'Latte',
        'Frutta a guscio',
        'Sedano',
        'Senape',
        'Sesamo',
        'Solfiti',
        'Lupini',
        'Molluschi',
    ];

    public function run(): void
    {
        foreach (self::ALLERGENS as $name) {
            Allergen::firstOrCreate(['name' => $name]);
        }
    }
}
