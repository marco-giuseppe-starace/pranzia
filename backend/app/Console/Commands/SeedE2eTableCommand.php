<?php

namespace App\Console\Commands;

use App\Models\DiningTable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

// Comando di supporto solo per i test E2E (Playwright): crea o riusa un
// tavolo con un qr_token noto, cosi' i test possono aprire la landing page
// senza dover passare da un endpoint HTTP di creazione tavoli (che non
// esiste, ne' dovrebbe esistere, come rotta pubblica).
#[Signature('e2e:seed-table {qr_token} {number=99}')]
#[Description('Crea (o riusa) un tavolo di prova con il qr_token dato, per i test E2E.')]
class SeedE2eTableCommand extends Command
{
    public function handle(): void
    {
        $table = DiningTable::firstOrCreate(
            ['qr_token' => $this->argument('qr_token')],
            ['number' => (int) $this->argument('number')]
        );

        $this->info("Tavolo pronto: id={$table->id}, qr_token={$table->qr_token}");
    }
}
