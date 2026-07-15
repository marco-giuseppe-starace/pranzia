# Pranzia — contesto progetto (per Claude Code)

## Cos'è
App self-service per un singolo ristorante italiano. Il cliente scansiona un QR
code al tavolo, consulta il menu, riceve consigli/traduzioni/filtri allergeni
dall'IA, e invia l'ordine direttamente in cucina. Nessuna registrazione richiesta.

## Nome e brand
- Nome: **Pranzia**, wordmark visualizzato come **PranzIA** (P maiuscola, "IA"
  maiuscola ed evidenziata in colore terracotta #D85A30)
- Domini registrati/da registrare: pranzia.it, pranzia.com (entrambi liberi al
  momento della ricerca)
- Font logo: Baloo 2 (Google Fonts, pesi 500/700) — riservato al wordmark, non
  all'interfaccia
- Font interfaccia: consigliato un sans neutro leggibile (Inter o system-ui)
- Colori: accento primario arancione caldo #EF9F27, testo/contorno su icona
  marrone scuro #412402, accento "IA" terracotta #D85A30
- Icona logo: forchetta e cucchiaio stilizzati a forma di "P"
- Tono di voce: caldo, diretto, conviviale — non robotico nonostante sia un
  prodotto IA
- Repository GitHub: https://github.com/marco-giuseppe-starace/pranzia

## Stack tecnologico
- Backend: Laravel 11 (API REST)
- Frontend: Vue 3 + PWA (nessuna installazione richiesta)
- Database: MySQL 8
- IA: Claude API (Haiku per traduzione/filtri, Sonnet per consigli se serve
  più qualità)
- Cache/code (opzionale ma consigliato): Redis

## Architettura
Cliente → QR code → PWA Vue → API Laravel → MySQL (menu, ordini) + Claude API
(consigli, traduzione)

Regola di sicurezza: la chiave API di Claude vive solo sul backend Laravel,
mai esposta al frontend.

## Funzionalità IA richieste
1. Consigli/upselling personalizzati (basati solo sul menu reale, mai inventati)
2. Traduzione multilingua per turisti
3. Gestione allergie/intolleranze — **critico**: gli allergeni vanno taggati
   manualmente e verificati dallo staff nel database, mai dedotti dall'IA;
   ogni risposta su allergie deve includere disclaimer "verifica con lo staff"

## Schema database (tabelle principali)
- **tables** — id, qr_token (univoco), number
- **table_sessions** — id, table_id (FK), language, status, started_at
- **menu_categories** — id, name, sort_order
- **menu_items** — id, category_id (FK), name, description, price, available, image_url
- **allergens** — id, name (14 allergeni UE standard)
- **menu_item_allergens** — pivot: menu_item_id (FK), allergen_id (FK), compilata manualmente
- **orders** — id, session_id (FK), status, total
- **order_items** — id, order_id (FK), menu_item_id (FK), quantity, notes, price_at_order
- **ai_interactions** — id, session_id (FK), type, tokens_input, tokens_output, cost_estimate, created_at

## Endpoint API principali
```
GET  /api/menu?lang=it
POST /api/session                   (avvia sessione da qr_token)
POST /api/ai/recommend
POST /api/ai/ask                    (traduzione, domande libere)
POST /api/orders
GET  /api/orders/{session_id}

--- Area admin (autenticata) ---
CRUD /api/admin/menu-items
CRUD /api/admin/allergens
GET  /api/admin/orders
GET  /api/admin/ai-costs
```

## Stima costi IA
~400 interazioni/giorno (100 coperti × 4 interazioni) → ~12.000/mese
- Con Haiku: ~€15-20/mese
- Con Sonnet: ~€45-50/mese

## Milestone di sviluppo (già tradotte in issue GitHub)
1. Setup & infrastruttura (repo, ambiente dev, CI)
2. Database e migration
3. Backend core (menu, sessioni, ordini, auth admin)
4. Integrazione IA (wrapper Claude, consigli, traduzione, filtro allergeni,
   guardrail, logging costi)
5. Frontend cliente (PWA: landing QR, menu, chat IA, carrello, lingua)
6. Pannello ristoratore (dashboard cucina, gestione menu, report costi IA)
7. Testing e deploy

## Stato attuale del progetto
- Nome, dominio, logo e brand guideline definiti
- Repository GitHub creato: marco-giuseppe-starace/pranzia
- File già preparati (da committare nel repo):
  - specifiche-tecniche.md (le stesse informazioni di questo documento, in
    formato più dettagliato)
  - crea-issues-github.sh (script gh CLI per popolare 25 issue in 7 milestone)
  - assets/brand-guidelines.md, assets/pranzia-icon.svg,
    assets/pranzia-logo-lockup.svg
- Prossimo passo: eseguire lo script delle issue, poi iniziare dallo sviluppo
  del Milestone 1 (setup Laravel + Vue)
