#!/usr/bin/env bash
# Popola le issue GitHub per il progetto "app ristorante IA".
#
# Prerequisiti:
#   1. Installa GitHub CLI:  https://cli.github.com
#   2. Autenticati:          gh auth login
#   3. Crea il repo (se non esiste già):
#        gh repo create app-ristorante-ia --private --source=. --remote=origin
#   4. Esegui questo script dalla root del repo:
#        chmod +x crea-issues-github.sh && ./crea-issues-github.sh

set -e

# Crea le milestone (etichette dei gruppi di lavoro)
gh label create "setup" --color "6E6E6E" --force
gh label create "database" --color "0F6E56" --force
gh label create "backend" --color "185FA5" --force
gh label create "ia" --color "854F0B" --force
gh label create "frontend" --color "993C1D" --force
gh label create "admin" --color "72243E" --force
gh label create "testing" --color "3B6D11" --force

create_issue () {
  gh issue create --title "$1" --body "$2" --label "$3"
}

# --- Milestone 1: Setup & infrastruttura ---
create_issue "Setup repo Laravel + Vue" "Inizializzare backend Laravel e frontend Vue (monorepo o repo separati). Configurare .env, git, README base." "setup"
create_issue "Ambiente di sviluppo" "Configurare Docker/Sail o ambiente locale equivalente per lo sviluppo." "setup"
create_issue "CI base" "Configurare pipeline CI con lint e test automatici (GitHub Actions)." "setup"

# --- Milestone 2: Database ---
create_issue "Migration: menu_categories, menu_items, allergens" "Creare le migration per le tabelle del menu e degli allergeni, con relazione many-to-many menu_item_allergens." "database"
create_issue "Migration: tables, table_sessions" "Creare le migration per i tavoli e le sessioni cliente (qr_token, language, status)." "database"
create_issue "Migration: orders, order_items, ai_interactions" "Creare le migration per ordini, righe ordine e log delle interazioni IA (per monitoraggio costi)." "database"
create_issue "Seeder menu di esempio" "Popolare il database con un menu di esempio realistico, incluse categorie e allergeni assegnati." "database"

# --- Milestone 3: Backend core ---
create_issue "API gestione menu (CRUD admin)" "Endpoint per creare/modificare/eliminare categorie e piatti dal pannello ristoratore." "backend"
create_issue "API sessione tavolo via QR" "Endpoint POST /api/session che avvia una sessione a partire dal qr_token scansionato." "backend"
create_issue "API creazione e gestione ordini" "Endpoint per creare l'ordine dal carrello e tracciarne lo stato in tempo reale." "backend"
create_issue "Autenticazione area admin" "Login per lo staff del ristorante per accedere al pannello di gestione." "backend"

# --- Milestone 4: Integrazione IA ---
create_issue "Servizio wrapper Claude API" "Classe/servizio Laravel che centralizza le chiamate a Claude, con gestione sicura della chiave API (mai esposta al frontend)." "ia"
create_issue "Endpoint consigli/upselling" "POST /api/ai/recommend: genera suggerimenti basati sul menu reale (mai piatti inventati) e sul contesto della sessione." "ia"
create_issue "Endpoint traduzione e domande libere" "POST /api/ai/ask: risponde a domande sui piatti e traduce nella lingua del cliente." "ia"
create_issue "Filtro allergeni basato su dati verificati" "Logica che incrocia le allergie dichiarate con i dati allergeni del database (mai dedotti dall'IA), con disclaimer obbligatorio." "ia"
create_issue "Prompt di sistema e guardrail" "Definire il system prompt che vincola l'IA al menu reale e impedisce risposte fuori contesto." "ia"
create_issue "Logging costi e token" "Salvare ogni chiamata IA in ai_interactions per monitorare la spesa mensile." "ia"

# --- Milestone 5: Frontend cliente ---
create_issue "Landing QR / avvio sessione" "Pagina che si apre dalla scansione del QR e avvia la sessione tavolo." "frontend"
create_issue "Visualizzazione menu" "Menu con categorie, filtri per allergeni, immagini piatti." "frontend"
create_issue "Chat/assistente IA" "Interfaccia conversazionale per consigli, domande e traduzione." "frontend"
create_issue "Carrello e invio ordine" "Gestione carrello, riepilogo e invio ordine in cucina." "frontend"
create_issue "Selezione lingua" "Switch lingua interfaccia con traduzione automatica dei contenuti." "frontend"

# --- Milestone 6: Pannello ristoratore ---
create_issue "Dashboard ordini (cucina)" "Vista in tempo reale degli ordini in arrivo, con cambio stato (in preparazione/servito)." "admin"
create_issue "Gestione menu da pannello" "CRUD piatti, categorie, allergeni e disponibilità dal pannello admin." "admin"
create_issue "Report costi IA" "Vista riepilogativa della spesa IA mensile per il ristoratore/sviluppatore." "admin"

# --- Milestone 7: Testing e deploy ---
create_issue "Test end-to-end flusso ordine" "Test che copre l'intero flusso: scan QR → menu → IA → ordine → cucina." "testing"
create_issue "Deploy produzione" "Hosting, SSL, dominio, configurazione ambiente di produzione." "testing"
create_issue "Documentazione README" "Documentare setup, architettura ed endpoint per manutenzione futura." "testing"

echo "Fatto! Issue create nel repository."
