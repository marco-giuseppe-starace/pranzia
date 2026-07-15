# Schema database

Database: MySQL 8, nome `pranzia`.

## Tabelle principali

**tables** — id, qr_token (univoco), number

**table_sessions** — id, table_id (FK), language, status (attiva/chiusa), started_at

**menu_categories** — id, name, sort_order

**menu_items** — id, category_id (FK), name, description, price, available (bool), image_url

**allergens** — id, name (i 14 allergeni UE standard: glutine, crostacei, uova,
pesce, arachidi, soia, latte, frutta a guscio, sedano, senape, sesamo, solfiti,
lupini, molluschi)

**menu_item_allergens** — pivot: menu_item_id (FK), allergen_id (FK)
→ Compilata **manualmente dallo staff**, mai dedotta dall'IA (vedi
`docs/ia-guardrail.md`).

**orders** — id, session_id (FK), status (in attesa/in preparazione/servito), total

**order_items** — id, order_id (FK), menu_item_id (FK), quantity, notes, price_at_order

**ai_interactions** — id, session_id (FK), type (consiglio/traduzione/allergene),
tokens_input, tokens_output, cost_estimate, created_at
→ Serve per monitorare la spesa mensile IA e ottimizzare il modello usato (vedi
`docs/ia-guardrail.md` e report costi in `/api/admin/ai-costs`).

## Stato
Le migration per queste tabelle non sono ancora state scritte (Milestone 2 —
issue GitHub #4-#7). Il backend attualmente contiene solo le migration di
default di Laravel (users, cache, jobs).
