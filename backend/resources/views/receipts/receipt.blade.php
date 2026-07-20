<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ricevuta - Tavolo {{ $table_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1a1a1a; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { text-align: left; padding: 4px 0; border-bottom: 1px solid #eee; vertical-align: top; }
        th.num, td.num { text-align: right; }
        .item-notes { color: #777; font-size: 11px; font-style: italic; margin-top: 2px; }
        tfoot td { border-bottom: none; padding-top: 6px; }
        .total-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #1a1a1a; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>PranzIA</h1>
    <p class="meta">
        Tavolo {{ $table_number }} &mdash; {{ \Illuminate\Support\Carbon::parse($paid_at)->format('d/m/Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Piatto</th>
                <th class="num">Qta</th>
                <th class="num">Prezzo</th>
                <th class="num">Totale</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>
                        {{ $item['name'] }}
                        @if (!empty($item['notes']))
                            <div class="item-notes">{{ $item['notes'] }}</div>
                        @endif
                    </td>
                    <td class="num">{{ $item['quantity'] }}</td>
                    <td class="num">{{ number_format($item['unit_price'], 2, ',', '.') }} &euro;</td>
                    <td class="num">{{ number_format($item['total'], 2, ',', '.') }} &euro;</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Subtotale piatti</td>
                <td class="num">{{ number_format($items_subtotal, 2, ',', '.') }} &euro;</td>
            </tr>
            @if ($cover_total > 0)
                <tr>
                    <td colspan="3">Coperto ({{ $guests }} x {{ number_format($cover_charge, 2, ',', '.') }} &euro;)</td>
                    <td class="num">{{ number_format($cover_total, 2, ',', '.') }} &euro;</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="3">Totale</td>
                <td class="num">{{ number_format($total, 2, ',', '.') }} &euro;</td>
            </tr>
        </tfoot>
    </table>

    <p class="meta">Grazie della visita!</p>
</body>
</html>
