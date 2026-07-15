# Pranzia

Ordina comodamente al tavolo grazie all'IA.

App self-service per un singolo ristorante italiano: il cliente scansiona un
QR code al tavolo, consulta il menu, riceve consigli/traduzioni/filtri
allergeni dall'IA, e invia l'ordine direttamente in cucina. Nessuna
registrazione richiesta.

## Struttura del repository
```
backend/     Laravel 13 (API REST)
frontend/     Vue 3 + Vite (PWA)
docs/          documentazione tecnica
assets/        brand (loghi, guideline)
```

## Documentazione
- [Architettura](docs/architettura.md)
- [Schema database](docs/database.md)
- [API](docs/api.md)
- [Regole IA (guardrail)](docs/ia-guardrail.md)
- [Setup ambiente locale](docs/setup-locale.md)
- [Deploy in produzione](docs/deploy.md)

## Quickstart
Vedi [docs/setup-locale.md](docs/setup-locale.md) per l'ambiente completo
(Laragon, MySQL, virtual host). In breve:

```sh
# Backend
cd backend && composer install && cp .env.example .env && php artisan key:generate && php artisan migrate

# Frontend
cd frontend && npm install && cp .env.example .env && npm run dev
```

## Test
```sh
# Backend (Pest)
cd backend && ./vendor/bin/pest

# Frontend — unit/componenti (Vitest)
cd frontend && npm run test

# Frontend — end-to-end (Playwright, richiede backend + frontend avviati)
cd frontend && npm run test:e2e
```

Tutti e tre girano anche in CI su ogni push/PR verso `main` (vedi
`.github/workflows/ci.yml`).

## Stato del progetto
Milestone 1-7 completate (setup, database, backend core, integrazione IA,
frontend cliente, pannello ristoratore, testing e deploy). Le milestone
successive sono tracciate come issue GitHub, organizzate nel
[project board Pranzia](https://github.com/users/marco-giuseppe-starace/projects/5).
