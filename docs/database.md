# Schema database

Database: MySQL 8, nome `pranzia`.

## Tabelle principali

**tables** — id, qr_token (univoco), number
→ Modello Eloquent: `App\Models\DiningTable` (nome classe diverso da `Table`
per evitare ambiguità col concetto generico di tabella; nome tabella DB resta
`tables`).

**table_sessions** — id, table_id (FK), language, status, started_at
→ `status` è un [PHP backed enum](../backend/app/Enums/TableSessionStatus.php)
(`active`/`closed`), con cast Eloquent nativo sul modello `TableSession`.

**menu_categories** — id, name, sort_order

**menu_items** — id, category_id (FK), name, description, price, available (bool), image_url

**allergens** — id, name (i 14 allergeni UE standard: glutine, crostacei, uova,
pesce, arachidi, soia, latte, frutta a guscio, sedano, senape, sesamo, solfiti,
lupini, molluschi)

**menu_item_allergens** — pivot: menu_item_id (FK), allergen_id (FK)
→ Compilata **manualmente dallo staff**, mai dedotta dall'IA (vedi
`docs/ia-guardrail.md`).

**orders** — id, session_id (FK), status, total
→ `status` è un [PHP backed enum](../backend/app/Enums/OrderStatus.php)
(`pending`/`preparing`/`served`), con cast Eloquent nativo sul modello `Order`.

**order_items** — id, order_id (FK), menu_item_id (FK), quantity, notes, price_at_order
→ `menu_item_id` usa `onDelete('restrict')`: uno storico ordini non perde il
riferimento al piatto anche se rimosso dal menu attuale.

**ai_interactions** — id, session_id (FK), type, tokens_input, tokens_output,
cost_estimate, created_at
→ `type` è un [PHP backed enum](../backend/app/Enums/AiInteractionType.php)
(`recommendation`/`translation`/`allergen_check`). Serve per monitorare la
spesa mensile IA e ottimizzare il modello usato (vedi `docs/ia-guardrail.md`
e report costi in `/api/admin/ai-costs`).

## Seeder
`database/seeders/AllergenSeeder.php` popola i 14 allergeni UE standard.
`database/seeders/MenuSeeder.php` popola un menu di esempio realistico
(5 categorie, 18 piatti) con allergeni assegnati coerentemente a ogni piatto.
Eseguiti entrambi da `DatabaseSeeder` con `php artisan migrate:fresh --seed`.

## Stato
Migration, modelli Eloquent e seeder sono implementati (Milestone 2 — issue
GitHub #4-#7 completate). Prossimo passo: Milestone 3, backend core (CRUD
menu, sessione tavolo via QR, creazione ordini, autenticazione admin).
