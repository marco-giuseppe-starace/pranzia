# App ordinazione ristorante con IA — Specifiche tecniche

## 1. Panoramica
App self-service per un singolo ristorante. Il cliente scansiona un QR code al tavolo,
consulta il menu, riceve consigli/traduzioni/filtri allergeni dall'IA, e invia l'ordine
direttamente in cucina. Nessuna registrazione richiesta.

## 2. Stack tecnologico
- Backend: Laravel 11 (API REST)
- Frontend: Vue 3 + PWA (nessuna installazione richiesta)
- Database: MySQL 8
- IA: Claude API (Haiku per traduzione/filtri, Sonnet per consigli se serve più qualità)
- Cache/code (opzionale ma consigliato): Redis, per sessioni tavolo e job asincroni

## 3. Architettura
Cliente → QR code → PWA Vue → API Laravel → MySQL (menu, ordini) + Claude API (consigli, traduzione)

Regola di sicurezza: la chiave API di Claude vive **solo** sul backend Laravel.
Il frontend non deve mai chiamare direttamente l'API IA.

## 4. Schema database (tabelle principali)

**tables** — id, qr_token (univoco), number

**table_sessions** — id, table_id (FK), language, status (attiva/chiusa), started_at

**menu_categories** — id, name, sort_order

**menu_items** — id, category_id (FK), name, description, price, available (bool), image_url

**allergens** — id, name (i 14 allergeni UE standard: glutine, crostacei, uova, pesce,
arachidi, soia, latte, frutta a guscio, sedano, senape, sesamo, solfiti, lupini, molluschi)

**menu_item_allergens** — pivot: menu_item_id (FK), allergen_id (FK)
→ Compilata **manualmente dallo staff**, mai dedotta dall'IA.

**orders** — id, session_id (FK), status (in attesa/in preparazione/servito), total

**order_items** — id, order_id (FK), menu_item_id (FK), quantity, notes, price_at_order

**ai_interactions** — id, session_id (FK), type (consiglio/traduzione/allergene),
tokens_input, tokens_output, cost_estimate, created_at
→ Serve per monitorare la spesa mensile IA e ottimizzare il modello usato.

## 5. Endpoint API principali

```
GET  /api/menu?lang=it              → menu con traduzioni e allergeni
POST /api/session                   → avvia sessione tramite qr_token
POST /api/ai/recommend              → { session_id, context } → suggerimento piatti
POST /api/ai/ask                    → { session_id, question } → risposta libera (traduzione, info piatto)
POST /api/orders                    → crea ordine da carrello
GET  /api/orders/{session_id}       → stato ordine in tempo reale

--- Area admin (autenticata) ---
CRUD /api/admin/menu-items
CRUD /api/admin/allergens
GET  /api/admin/orders              → dashboard cucina
GET  /api/admin/ai-costs            → report spesa IA mensile
```

## 6. Regole di sicurezza per l'IA (fondamentali)
1. Il prompt di sistema deve vincolare l'IA a rispondere **solo** sulla base del menu
   passato come contesto strutturato — mai inventare piatti o prezzi.
2. Le informazioni su allergeni provengono **sempre** dal database verificato dallo
   staff, mai da un'inferenza del modello.
3. Ogni risposta relativa ad allergie deve includere un disclaimer: "verifica con lo
   staff in caso di allergie gravi".
4. Loggare ogni chiamata IA (tokens, costo) per controllo di spesa.
5. Rate limiting per sessione tavolo, per evitare abusi/costi eccessivi.

## 7. Stima costi IA
~400 interazioni/giorno (100 coperti × 4 interazioni) → ~12.000/mese
- Con Haiku: ~€15-20/mese
- Con Sonnet: ~€45-50/mese

## 8. Milestone di sviluppo
1. Setup & infrastruttura
2. Database e migration
3. Backend core (menu, sessioni, ordini)
4. Integrazione IA (consigli, traduzione, allergeni)
5. Frontend cliente (PWA)
6. Pannello ristoratore (dashboard cucina, gestione menu)
7. Testing e deploy
