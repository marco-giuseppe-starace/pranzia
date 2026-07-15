# API

Tutte le rotte sono servite dal backend Laravel su `pranzia.test` (locale) o
dal dominio di produzione. Il frontend Vue le chiama tramite `VITE_API_URL`
(vedi `frontend/.env.example`).

## Endpoint pubblici (cliente)

```
GET  /api/menu?lang=it              → menu con traduzioni e allergeni
POST /api/session                   → avvia sessione tramite qr_token
POST /api/ai/recommend              → { session_id, context } → suggerimento piatti
POST /api/ai/ask                    → { session_id, question } → risposta libera (traduzione, info piatto)
POST /api/orders                    → crea ordine da carrello
GET  /api/orders/{session_id}       → stato ordine in tempo reale
```

## Endpoint area admin (autenticata)

```
CRUD /api/admin/menu-items
CRUD /api/admin/allergens
GET  /api/admin/orders              → dashboard cucina
GET  /api/admin/ai-costs            → report spesa IA mensile
```

## Stato
Nessuno di questi endpoint è ancora implementato: il backend contiene solo lo
scaffold Laravel di default. Verranno implementati nella Milestone 3 (backend
core) e Milestone 4 (integrazione IA) — vedi le issue GitHub corrispondenti.
