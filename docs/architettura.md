# Architettura

## Panoramica
Pranzia è un'app self-service per un singolo ristorante italiano: il cliente
scansiona un QR code al tavolo, consulta il menu, riceve consigli, traduzioni
e filtri allergeni dall'IA, e invia l'ordine direttamente in cucina. Nessuna
registrazione richiesta.

## Flusso
```
Cliente → QR code → PWA Vue → API Laravel → MySQL (menu, ordini)
                                          → Claude API (consigli, traduzione)
```

## Stack tecnologico
- **Backend**: Laravel 13 (API REST) — vedi `backend/`
- **Frontend**: Vue 3 + Vite, PWA installabile senza app store — vedi
  `frontend/`. Router `vue-router`, stato `pinia` (sessione + carrello,
  persistiti in `localStorage`), i18n statico minimale per it/en, client
  HTTP fetch-based (nessun axios). Sezione `/admin/*` (staff) con auth
  Sanctum a token, layout separato (`AdminLayout.vue`) dal layout cliente
- **Database**: MySQL 8
- **IA**: Claude API (Haiku per traduzione/filtri, Sonnet per consigli se serve
  più qualità)
- **Cache/code** (opzionale ma consigliato): Redis, per sessioni tavolo e job
  asincroni

> Nota: le specifiche iniziali del progetto indicavano Laravel 11. È stato
> aggiornato a Laravel 13 in fase di setup (luglio 2026) perché il branch 11
> risultava affetto da vulnerabilità di sicurezza (CVE-2026-48019 e un path
> confusion nelle signed URL) senza patch disponibili su quel branch.

## Regole di sicurezza fondamentali
1. La chiave API di Claude vive **solo** sul backend Laravel. Il frontend non
   deve mai chiamare direttamente l'API IA.
2. Il prompt di sistema vincola l'IA a rispondere solo sulla base del menu
   reale passato come contesto strutturato — mai inventare piatti o prezzi.
3. Vedi `docs/ia-guardrail.md` per il dettaglio completo delle regole IA.

## Repository
Monorepo su GitHub: https://github.com/marco-giuseppe-starace/pranzia

```
pranzia/
├── backend/     # Laravel 13 API
├── frontend/     # Vue 3 + Vite + PWA
├── docs/          # questa documentazione
├── assets/        # brand (loghi, guideline)
└── .github/workflows/ci.yml
```
