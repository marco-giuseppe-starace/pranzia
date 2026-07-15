# API

Tutte le rotte sono servite dal backend Laravel su `pranzia.test` (locale) o
dal dominio di produzione. Il frontend Vue le chiama tramite `VITE_API_URL`
(vedi `frontend/.env.example`).

## Endpoint pubblici (cliente)

```
GET  /api/menu                      → menu con categorie, piatti e allergeni
POST /api/session                   → { qr_token } → avvia/riprende la sessione del tavolo
POST /api/orders                    → { session_id, items: [...] } → crea ordine da carrello
GET  /api/orders/{session_id}       → stato ordini di quella sessione
```

> **Limite noto**: il parametro `lang` sul menu non è ancora implementato —
> il menu è restituito solo in italiano. La traduzione automatica arriva
> nella Milestone 4 (integrazione IA), insieme a `/api/ai/recommend` e
> `/api/ai/ask`.

Dettagli `POST /api/orders`:
- il prezzo di ogni riga (`price_at_order`) è **sempre** letto dal prezzo
  corrente del piatto nel menu, mai da un valore passato dal client
- rifiuta con `422` se la sessione non è attiva o se un piatto non è
  disponibile (`available: false`)

## Autenticazione area admin

```
POST /api/admin/login                → { email, password } → { token }
POST /api/admin/logout                → revoca il token corrente (auth:sanctum)
```

Autenticazione **Sanctum a token Bearer** (non SPA/cookie): il frontend salva
il token e lo invia come header `Authorization: Bearer <token>`. Scelto per
semplicità, dato che frontend e backend girano su origini diverse in
sviluppo (Vite dev server vs `pranzia.test`).

## Endpoint area admin (autenticati, `auth:sanctum`)

```
GET|POST /api/admin/menu-items
PUT      /api/admin/menu-items/{menu_item}
DELETE   /api/admin/menu-items/{menu_item}

GET|POST /api/admin/allergens
PUT      /api/admin/allergens/{allergen}
DELETE   /api/admin/allergens/{allergen}

GET      /api/admin/orders                → dashboard cucina, ?status= opzionale
```

`/api/admin/ai-costs` non è ancora implementato: arriverà nella Milestone 4
insieme al logging delle chiamate IA.

## Stato
Milestone 3 (backend core) completata: tutti gli endpoint sopra sono
implementati e coperti da test Pest (`tests/Feature/Api/`). Prossimo passo:
Milestone 4, integrazione IA (Claude API, consigli, traduzione, filtro
allergeni, guardrail, logging costi).
