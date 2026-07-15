# Regole IA (guardrail)

Regole vincolanti per qualunque funzionalità che usa Claude API in Pranzia.

1. **Chiave API solo backend.** La chiave Claude vive esclusivamente nel
   backend Laravel (`ANTHROPIC_API_KEY` in `.env`), mai esposta al frontend o
   nel bundle Vue. `App\Services\Ai\AnthropicSdkClient` è l'unico punto che
   istanzia l'SDK.

2. **Mai piatti o prezzi inventati.** Il system prompt (`SystemPromptBuilder`)
   passa il menu reale come contesto JSON strutturato (query dal database) e
   vincola esplicitamente l'IA a rispondere solo su quella base.

3. **Allergeni: dato verificato, non inferenza.** Le informazioni sugli
   allergeni provengono **sempre** dalla tabella `menu_item_allergens`,
   compilata manualmente e verificata dallo staff — mai da un'inferenza del
   modello IA. Vedi `docs/database.md`. Il filtro `GET /api/menu?exclude_allergens=`
   è puramente lato database, nessuna chiamata IA coinvolta.

4. **Disclaimer obbligatorio.** Il system prompt istruisce l'IA a includere,
   testuale, in ogni risposta relativa ad allergie: *"Verifica sempre con lo
   staff in caso di allergie gravi."*

5. **Logging costi.** Ogni chiamata IA (riuscita o fallita) crea un record in
   `ai_interactions` (tokens input/output, costo stimato) — vedi
   `AiAssistantService::log()` e il report `GET /api/admin/ai-costs`.

6. **Rate limiting.** `POST /api/ai/recommend` e `POST /api/ai/ask` sono
   limitati a 10 richieste/minuto **per sessione tavolo** (non per IP), per
   non penalizzare un tavolo condiviso da più persone.

7. **Conto del tavolo: dato reale, non stimato.** Il totale già speso dal
   tavolo (somma di `orders.total` per la sessione) viene passato nel
   system prompt come unica fonte di verità, così l'IA può rispondere a
   domande su spesa e divisione del conto tra più persone (es. "quanto
   abbiamo speso?", "dividiamo per 4?") senza mai ricalcolare o inventare
   importi. Vedi `AiAssistantService::call()` e `SystemPromptBuilder`.

## Modelli usati
- **Haiku** (`claude-haiku-4-5`): `POST /api/ai/ask` — traduzione e domande
  libere sui piatti (alto volume, task semplici)
- **Sonnet** (`claude-sonnet-4-6`): `POST /api/ai/recommend` — consigli e
  upselling, dove serve più qualità di ragionamento

Configurabili via `ANTHROPIC_MODEL_ASK` / `ANTHROPIC_MODEL_RECOMMEND` in
`.env` (default sopra).

## Stima costi
~400 interazioni/giorno (100 coperti × 4 interazioni) → ~12.000/mese
- Con Haiku: ~€15-20/mese
- Con Sonnet: ~€45-50/mese

Costo reale monitorabile in tempo reale via `GET /api/admin/ai-costs`.

## Stato
Milestone 4 (integrazione IA) completata: wrapper Claude API, endpoint
consigli/domande, filtro allergeni, guardrail nel prompt, logging costi e
rate limiting sono tutti implementati e testati (con client Claude finto,
nessuna chiamata reale nei test). **Per l'uso reale serve una
`ANTHROPIC_API_KEY` valida in `backend/.env`.**
