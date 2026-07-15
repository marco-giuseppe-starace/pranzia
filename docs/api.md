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

`GET /api/menu` accetta `?exclude_allergens=1,7` per escludere i piatti che
contengono uno o più allergeni (dato verificato in DB, nessuna IA coinvolta).

> **Limite noto**: il parametro `lang` sul menu non ha ancora effetto — il
> menu è restituito solo in italiano. La traduzione avviene tramite
> `POST /api/ai/ask`, non pre-traducendo l'intero menu.

Dettagli `POST /api/orders`:
- il prezzo di ogni riga (`price_at_order`) è **sempre** letto dal prezzo
  corrente del piatto nel menu, mai da un valore passato dal client
- rifiuta con `422` se la sessione non è attiva o se un piatto non è
  disponibile (`available: false`)

## Endpoint IA pubblici (cliente)

```
POST /api/ai/recommend    → { session_id, context? } → { text } (Sonnet)
POST /api/ai/ask          → { session_id, question, language? } → { text } (Haiku)
```

- `recommend`: consigli/upselling basati solo sul menu reale disponibile
- `ask`: traduzione e domande libere sui piatti, con disclaimer allergeni
  automatico quando pertinente (vedi `docs/ia-guardrail.md`). `language`
  (opzionale, es. "en") sovrascrive la lingua di sessione solo per quella
  chiamata — il frontend invia la lingua correntemente selezionata
  nell'interfaccia
- Rifiutano con `422` se la sessione non è attiva
- Rifiutano con `502` (messaggio generico, mai il dettaglio dell'eccezione)
  se la chiamata a Claude fallisce (es. chiave API mancante/non valida) — la
  chiamata fallita viene comunque loggata in `ai_interactions` con costo 0
- Rate limit: **10 richieste/minuto per sessione tavolo** (non per IP) — oltre
  restituisce `429`
- Richiedono `ANTHROPIC_API_KEY` valida in `backend/.env` per funzionare
  davvero

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
GET      /api/admin/ai-costs              → report spesa IA, raggruppato per mese e tipo
```

## Stato
Milestone 5 (frontend cliente) completata: tutti gli endpoint sopra sono
implementati, coperti da test Pest (`tests/Feature/Api/`, client Claude finto
nei test) e consumati dalla PWA Vue in `frontend/`. Prossimo passo: Milestone
6, pannello ristoratore (dashboard cucina, gestione menu, report costi IA).
