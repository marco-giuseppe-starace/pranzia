<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; color: #1a1a1a;">
    <h1 style="color: #412402;">PranzIA</h1>
    <p>Ecco la ricevuta per il Tavolo {{ $receipt['table_number'] }}.</p>
    <p>
        Totale: <strong>{{ number_format($receipt['total'], 2, ',', '.') }} &euro;</strong>
    </p>
    <p>Trovi il dettaglio completo nel PDF allegato. Grazie della visita!</p>
</body>
</html>
