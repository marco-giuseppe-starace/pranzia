<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashRegisterTableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $session = $this->activeSession;

        return [
            'id' => $this->id,
            'number' => $this->number,
            'session_id' => $session->id,
            'started_at' => $session->started_at,
            // Somma di tutti gli ordini della sessione, a prescindere dallo
            // stato cucina (in attesa/in preparazione/servito): al momento
            // di incassare, tutto cio' che e' stato ordinato va pagato.
            'total' => $session->orders->sum('total'),
            'order_count' => $session->orders->count(),
            // Gia' inserito dal cliente prima di ordinare (puo' essere
            // ancora null se e' riuscito ad aggirare il modal, es. una
            // sessione aperta prima di questa funzione): lo staff lo vede
            // pre-compilato e puo' comunque correggerlo.
            'guests' => $session->guests,
            'receipt_sent' => (bool) $session->receipt_sent_at,
        ];
    }
}
