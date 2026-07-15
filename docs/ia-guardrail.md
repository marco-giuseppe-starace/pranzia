# Regole IA (guardrail)

Regole vincolanti per qualunque funzionalità che usa Claude API in Pranzia.

1. **Chiave API solo backend.** La chiave Claude vive esclusivamente nel
   backend Laravel, mai esposta al frontend o nel bundle Vue.

2. **Mai piatti o prezzi inventati.** Il prompt di sistema deve vincolare
   l'IA a rispondere solo sulla base del menu reale passato come contesto
   strutturato (query dal database, non testo libero).

3. **Allergeni: dato verificato, non inferenza.** Le informazioni sugli
   allergeni provengono **sempre** dalla tabella `menu_item_allergens`,
   compilata manualmente e verificata dallo staff — mai da un'inferenza del
   modello IA. Vedi `docs/database.md`.

4. **Disclaimer obbligatorio.** Ogni risposta relativa ad allergie deve
   includere: *"verifica con lo staff in caso di allergie gravi"*.

5. **Logging costi.** Ogni chiamata IA va loggata in `ai_interactions`
   (tokens input/output, costo stimato) per il monitoraggio della spesa
   mensile — vedi endpoint `/api/admin/ai-costs`.

6. **Rate limiting.** Limitare le chiamate IA per sessione tavolo, per
   evitare abusi e costi eccessivi.

## Modelli usati
- **Haiku**: traduzione, filtri allergeni (task semplici, alto volume)
- **Sonnet**: consigli/upselling quando serve più qualità di ragionamento

## Stima costi
~400 interazioni/giorno (100 coperti × 4 interazioni) → ~12.000/mese
- Con Haiku: ~€15-20/mese
- Con Sonnet: ~€45-50/mese

## Stato
Nessuna di queste regole è ancora implementata nel codice: sarà oggetto della
Milestone 4 (integrazione IA) — issue GitHub "Servizio wrapper Claude API",
"Prompt di sistema e guardrail", "Filtro allergeni basato su dati verificati",
"Logging costi e token".
