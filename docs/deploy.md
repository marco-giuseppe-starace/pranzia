# Deploy in produzione

Questa guida copre come portare Pranzia in produzione. **Nessun deploy reale
è stato eseguito**: qui sotto trovi documentazione e file di configurazione,
non un'infrastruttura già attiva.

## Requisiti minimi
- PHP 8.3+ con le estensioni standard di Laravel (pdo_mysql, mbstring, ecc.)
- MySQL 8
- Node 22+ (solo in fase di build del frontend, non serve a runtime)
- Composer 2
- Un web server (Nginx consigliato) con PHP-FPM

## Hosting consigliato per questa scala
Pranzia è pensato per un singolo ristorante, con un volume di traffico
contenuto (vedi la stima in `docs/ia-guardrail.md`). Non serve
un'infrastruttura complessa — niente Kubernetes, niente load balancer:

- **Opzione semplice**: un VPS economico (Hetzner, DigitalOcean, ecc.) con
  Nginx + PHP-FPM + MySQL configurati a mano o con uno script di
  provisioning
- **Opzione gestita**: un hosting Laravel dedicato (es. Laravel Forge/Cloud)
  che si occupa di provisioning, certificati SSL e deploy da Git

## Build

### Backend
```sh
cd backend
composer install --no-dev --optimize-autoloader
cp .env.production.example .env   # poi compila i valori reali, vedi sotto
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend
```sh
cd frontend
npm ci
VITE_API_URL=https://pranzia.it/api npm run build
```

L'output (`frontend/dist/`) è statico: va servito da Nginx (o equivalente)
come root del sito, con un proxy per `/api` verso PHP-FPM/Laravel.

## Dominio e SSL
- Dominio: `pranzia.it` / `pranzia.com` (vedi `docs/architettura.md` per il
  brand)
- Certificato via **Let's Encrypt / Certbot**, rinnovo automatico
- **HTTPS obbligatorio**, non opzionale: la PWA richiede un contesto sicuro
  per registrare il service worker e risultare installabile (vedi
  `frontend/vite.config.js`, plugin PWA)
- Redirect HTTP → HTTPS a livello di web server

## Variabili d'ambiente di produzione
Vedi `backend/.env.production.example` per il template completo. I punti
critici rispetto a `.env.example` (sviluppo):

| Variabile | Sviluppo | Produzione |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | **`false`** — fondamentale, non deve mai esporre stack trace agli utenti |
| `APP_URL` | `http://pranzia.test` | `https://pranzia.it` (dominio reale) |
| `ANTHROPIC_API_KEY` | vuota o chiave di test | chiave reale, mai committata |
| `DB_*` | Laragon locale | credenziali del DB di produzione |

`SESSION_DRIVER`, `CACHE_STORE` e `QUEUE_CONNECTION` restano su `database`
(invariati) — va bene per questa scala, non serve Redis finché il traffico
non cresce sensibilmente.

## CI/CD
La pipeline attuale (`.github/workflows/ci.yml`) esegue solo test (Pest,
Vitest, Playwright E2E) — **nessun deploy automatico** è configurato in
questa fase. Un deploy automatico da CI (es. via SSH/rsync dopo i test verdi
su `main`, o un webhook verso l'hosting) è un passo naturale successivo, ma
richiede un target reale (server, credenziali) che non è disponibile ora.

## Checklist pre-lancio
- [ ] `APP_DEBUG=false` verificato in produzione
- [ ] `ANTHROPIC_API_KEY` reale configurata, mai loggata né esposta
- [ ] HTTPS attivo e redirect HTTP→HTTPS funzionante
- [ ] Migrazioni eseguite (`php artisan migrate --force`) e seeder del menu
  reale (non quello demo di `MenuSeeder`) caricato
- [ ] Backup del database configurato
- [ ] Monitoraggio errori/log (anche solo i log file di Laravel, verificati
  periodicamente) in atto
