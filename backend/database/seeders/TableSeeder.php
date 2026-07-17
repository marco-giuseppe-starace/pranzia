<?php

namespace Database\Seeders;

use App\Models\DiningTable;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    // Tavoli demo con qr_token prevedibili (tavolo-1..tavolo-10), cosi'
    // la pagina pubblica /demo puo' linkare direttamente a /t/tavolo-N
    // senza dover interrogare un endpoint per scoprirli.
    public function run(): void
    {
        for ($number = 1; $number <= 10; $number++) {
            DiningTable::firstOrCreate(
                ['qr_token' => "tavolo-{$number}"],
                ['number' => $number]
            );
        }
    }
}
