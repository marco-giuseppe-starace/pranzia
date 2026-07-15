# Pranzia — Brand guideline (mini)

## Nome
**Pranzia** — wordmark visualizzato come **PranzIA** (P maiuscola, IA maiuscola
ed evidenziata in colore), per richiamare l'intelligenza artificiale integrata
nel prodotto. Il dominio e i riferimenti tecnici restano minuscoli
(`pranzia.it`, `pranzia.com`, `pranzia_app` ecc.) — solo il logo/wordmark
usa il trattamento con maiuscole.

## Logo
Icona: forchetta e cucchiaio stilizzati che formano la lettera "P".

File inclusi:
- `pranzia-icon.svg` — icona standalone (app icon, favicon)
- `pranzia-logo-lockup.svg` — icona + wordmark, per header/intestazioni

Uso consigliato:
- Icona da sola: app icon, favicon, avatar, elementi piccoli
- Lockup orizzontale: header della PWA, pannello admin, materiali di comunicazione
- Non deformare, ruotare o cambiare le proporzioni tra icona e testo

## Colori

| Ruolo | Colore | Hex |
|---|---|---|
| Accento primario (sfondo icona) | Arancione caldo | `#EF9F27` |
| Testo/contorno su icona | Marrone scuro | `#412402` |
| Accento "IA" nel wordmark | Terracotta | `#D85A30` |
| Testo principale | Quasi nero | `#1A1A1A` |

## Font
**Baloo 2** (Google Fonts) — pesi 500 e 700.
Font arrotondato e caldo, adatto al contesto cibo/ospitalità.

```html
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700&display=swap" rel="stylesheet">
```

Per il testo dell'interfaccia (menu, bottoni, corpo del testo) è comunque
consigliabile un font più neutro e leggibile (es. Inter, system-ui) —
Baloo 2 va riservato al logo/wordmark, non a tutta l'interfaccia, per non
appesantire la leggibilità nei blocchi di testo lunghi.

## Tono di voce
Caldo, diretto, conviviale — mai eccessivamente tecnico o robotico,
nonostante il prodotto sia basato su IA. Il linguaggio dell'app deve
sentirsi come un consiglio di un amico esperto di cibo, non come
un'interfaccia da software gestionale.
