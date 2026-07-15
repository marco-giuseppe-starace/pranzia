# Setup ambiente locale (Laragon)

Ambiente di sviluppo basato su **Laragon** (PHP, MySQL, Apache), non Docker.

## Prerequisiti verificati
- PHP 8.3+ (fornito da Laragon)
- Composer 2.9+
- Node 22+ e npm
- MySQL 8 (fornito da Laragon, istanza condivisa con altri progetti sulla
  stessa macchina)

## 1. Database
Il progetto usa il database MySQL di default di Laragon, porta **3306**
(nessuna porta custom: Laragon gestisce un'unica istanza MySQL condivisa da
tutti i progetti, cambiare la porta romperebbe gli altri progetti).

Crea il database (una tantum):
```sh
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pranzia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## 2. Backend (Laravel)
```sh
cd backend
composer install
cp .env.example .env   # gia' configurato con DB_CONNECTION=mysql, DB_PORT=3306, DB_DATABASE=pranzia
php artisan key:generate
php artisan migrate
```

Il backend non gira sulla porta di default di `php artisan serve`, ma tramite
un **virtual host Apache di Laragon**: `http://pranzia.test`.

Per configurarlo (una tantum, richiede privilegi amministrativi):

1. Aggiungi al file hosts di Windows (`C:\Windows\System32\drivers\etc\hosts`,
   serve PowerShell da amministratore):
   ```powershell
   Add-Content -Path "$env:SystemRoot\System32\drivers\etc\hosts" -Value "127.0.0.1 pranzia.test"
   ```
2. Il virtual host Apache è già presente in
   `C:\laragon\etc\apache2\sites-enabled\auto.pranzia.test.conf`, con
   `DocumentRoot` puntato a `backend/public`.
3. Ricarica Apache da Laragon (tray icon → Apache → Reload).

Verifica: `http://pranzia.test` deve rispondere con la welcome page di Laravel.

### Test
```sh
cd backend
./vendor/bin/pest
```

## 3. Frontend (Vue + Vite)
```sh
cd frontend
npm install
cp .env.example .env   # VITE_API_URL=http://pranzia.test/api
npm run dev
```

Il dev server Vite gira sulla sua porta di default (`5173`, nessuna porta
custom) e fa da proxy delle chiamate `/api` verso `http://pranzia.test`
(vedi `frontend/vite.config.js`).

## Perché niente porte custom
In una fase iniziale si era ipotizzato di usare porte non standard (8765 per
il web, 9876 per MySQL) per evitare conflitti con altri progetti sulla stessa
macchina. La decisione finale è stata di **non cambiare le porte**: MySQL
resta condiviso sulla porta 3306 di default, e il conflitto tra progetti si
evita con virtual host diversi (`pranzia.test` invece di `localhost`) invece
che con porte diverse.
