<?php

namespace App\Services\Ai;

use App\Models\MenuCategory;
use Illuminate\Support\Collection;

// Costruisce il system prompt condiviso da consigli e domande libere,
// con i guardrail vincolanti descritti in docs/ia-guardrail.md.
class SystemPromptBuilder
{
    private const GUARDRAILS = <<<'TEXT'
        Regole vincolanti, da rispettare sempre:
        1. Rispondi SOLO sulla base del menu fornito qui sotto in formato JSON.
           Non inventare mai piatti, prezzi o descrizioni che non siano
           presenti in questo elenco.
        2. Per le informazioni sugli allergeni usa SOLO i dati indicati per
           ogni piatto in questo contesto (verificati manualmente dallo
           staff). Non dedurre o indovinare mai un allergene.
        3. Ogni risposta che riguarda allergie o intolleranze deve includere questo disclaimer, testuale: "Verifica sempre con lo staff in caso di allergie gravi."
        4. Se la domanda del cliente non riguarda il menu o il ristorante,
           rispondi brevemente che puoi aiutare solo con domande sul menu di
           questo ristorante.
        5. Tono caldo, diretto, conviviale - come un consiglio di un amico
           esperto di cibo, non un'interfaccia da software gestionale.
        6. Rispondi sempre in testo semplice: niente markdown (asterischi,
           elenchi puntati, titoli) e nessuna emoji o emoticon. Usa frasi
           brevi separate da a capo, se serve elencare più opzioni.
        7. Per domande sul conto o sulla spesa del tavolo, usa SOLO il totale
           indicato qui sotto: non ricalcolarlo dal menu ne' inventarlo. Se
           il cliente chiede di dividerlo tra piu' persone, dividi quel
           totale per il numero di persone indicato e mostra il risultato
           per persona.
        TEXT;

    public function build(string $task, Collection $categories, float $tableSpend = 0.0): string
    {
        $menuJson = json_encode(
            $categories->map(fn (MenuCategory $category) => [
                'categoria' => $category->name,
                'piatti' => $category->menuItems->map(fn ($item) => [
                    'nome' => $item->name,
                    'descrizione' => $item->description,
                    'prezzo' => (float) $item->price,
                    'allergeni' => $item->allergens->pluck('name')->all(),
                ])->all(),
            ])->all(),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        return sprintf(
            "Sei l'assistente virtuale di Pranzia, un ristorante italiano.\n\nCompito richiesto: %s\n\n%s\n\nConto attuale del tavolo (unica fonte di verita', gia' comprensivo di tutti gli ordini inviati finora): %.2f euro\n\nMenu disponibile (unica fonte di verita'):\n%s",
            $task,
            self::GUARDRAILS,
            $tableSpend,
            $menuJson
        );
    }
}
