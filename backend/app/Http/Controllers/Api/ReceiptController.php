<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ReceiptNotAvailableException;
use App\Http\Controllers\Controller;
use App\Mail\ReceiptMail;
use App\Models\Setting;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function show(TableSession $tableSession): JsonResponse
    {
        return response()->json($this->receiptData($tableSession));
    }

    public function pdf(TableSession $tableSession)
    {
        $pdf = Pdf::loadView('receipts.receipt', $this->receiptData($tableSession));

        return $pdf->download("ricevuta-tavolo-{$tableSession->table->number}.pdf");
    }

    public function email(Request $request, TableSession $tableSession): JsonResponse
    {
        $data = $this->receiptData($tableSession);
        $email = $request->validate(['email' => ['required', 'email']])['email'];

        $pdf = Pdf::loadView('receipts.receipt', $data)->output();
        Mail::to($email)->send(new ReceiptMail($data, $pdf));

        return response()->json(['message' => 'Ricevuta inviata.']);
    }

    // Dati della ricevuta: usati sia dalla risposta JSON (anteprima lato
    // cliente) sia dal template PDF (download ed email), cosi' i tre
    // canali mostrano sempre esattamente lo stesso conto.
    private function receiptData(TableSession $tableSession): array
    {
        if (! $tableSession->paid_at) {
            throw new ReceiptNotAvailableException();
        }

        $tableSession->loadMissing('table', 'orders.items.menuItem');

        $lines = $tableSession->orders->flatMap(
            fn ($order) => $order->items->map(fn ($item) => [
                'name' => $item->menuItem->name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->price_at_order,
                'total' => round((float) $item->price_at_order * $item->quantity, 2),
            ])
        )->values();

        $itemsSubtotal = round((float) $tableSession->orders->sum('total'), 2);
        $coverCharge = (float) Setting::get('cover_charge', '0');
        $coverTotal = round($coverCharge * $tableSession->guests, 2);

        return [
            'session_id' => $tableSession->id,
            'table_number' => $tableSession->table->number,
            'paid_at' => $tableSession->paid_at,
            'guests' => $tableSession->guests,
            'cover_charge' => $coverCharge,
            'cover_total' => $coverTotal,
            'items' => $lines,
            'items_subtotal' => $itemsSubtotal,
            'total' => round($itemsSubtotal + $coverTotal, 2),
        ];
    }
}
