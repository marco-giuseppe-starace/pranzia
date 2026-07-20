# Come funziona l'ordine al tavolo

Documento di riferimento per scrivere la sezione "Come funziona" della home
page di presentazione. Descrive il flusso cliente cosi' come e' realmente
implementato, non un'ipotesi.

## La scena

Il cliente entra nel locale, si siede, chiede il menu. Il tavolo ha un QR
code fisso (uno per tavolo, sempre lo stesso, stampato/esposto lì). Da qui in
poi ci sono **due modi equivalenti** di procedere, e possono anche mescolarsi
liberamente all'interno dello stesso pasto:

1. **Un solo telefono per tutto il tavolo** — una persona scansiona,
   ordina per tutti (anche in più invii separati, es. prima gli antipasti,
   poi il resto).
2. **Ogni persona scansiona dal proprio telefono** — ognuno vede lo stesso
   menu, sceglie i propri piatti e invia il proprio ordine in autonomia.

Entrambi i modi portano allo stesso risultato: **un unico conto per il
tavolo**, non un conto per persona.

## Perché funziona: la sessione condivisa

Il QR code non identifica una persona, identifica un **tavolo**. Quando
qualcuno lo scansiona, il sistema cerca se quel tavolo ha già una sessione
aperta (cioè: qualcuno di quel tavolo ha già scansionato prima, in questo
stesso pasto) e la riusa invece di crearne una nuova. Risultato pratico:

- Le prime 5 persone che scansionano lo stesso QR entrano tutte nella
  **stessa sessione tavolo**, non in 5 sessioni separate.
- Ogni ordine inviato — da qualunque telefono — viene registrato su quella
  sessione condivisa.
- Il conto finale (in cassa) somma **tutti** gli ordini della sessione,
  indipendentemente da chi li ha inviati.

## Cosa vede ciascun cliente

- **Il carrello prima dell'invio è privato**: quello che stai per ordinare
  (ma non hai ancora inviato) sta solo sul tuo telefono. Se il tuo vicino di
  tavolo scansiona lo stesso QR, non vede il tuo carrello — così ognuno può
  scegliere con calma senza interferire con gli altri.
- **Una volta inviato, l'ordine è condiviso**: appena qualcuno del tavolo
  invia un ordine, quell'ordine (piatti, stato di preparazione) diventa
  visibile a tutti i telefoni collegati a quella sessione, aggiornato in
  automatico ogni pochi secondi. Il tavolo vede quindi, mano a mano,
  l'elenco completo di tutto ciò che è stato ordinato da chiunque.
- Ogni persona può ordinare **più volte** nel corso del pasto (es.
  prima i primi, poi un dolce): ogni invio si aggiunge alla stessa sessione.

## Il conto e i coperti

Quando il tavolo ha finito ed è pronto a pagare, lo staff **incassa il
tavolo** dalla cassa: il totale è la somma di tutti gli ordini della
sessione (a prescindere da quante persone/telefoni li hanno inviati). In
quel momento lo staff indica anche **quante persone** sono sedute al
tavolo, per calcolare correttamente il coperto — questo numero lo decide
lo staff guardando il tavolo, non viene dedotto da quante persone hanno
scansionato il QR (non sarebbe affidabile: c'è chi non scansiona pur
essendo seduto, chi scansiona per tutti gli altri, ecc.).

Dopo l'incasso, la ricevuta (scaricabile in PDF o inviabile via email)
diventa disponibile su **tutti** i telefoni ancora collegati a quella
sessione — chiunque al tavolo può quindi scaricarsela, non solo chi ha
incassato/pagato materialmente.

## Riepilogo in una frase

> Un QR per tavolo, sessione condivisa: ognuno ordina dal proprio telefono
> (o uno ordina per tutti), tutti gli ordini finiscono in un unico conto,
> e la ricevuta finale è disponibile per chiunque al tavolo.
