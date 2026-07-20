<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Lanciata provando a chiudere (liberare) un tavolo che ha ancora ordini
// non "Servito": senza questo controllo l'ordine resta "orfano" (tavolo
// libero ma ordine ancora in attesa/in preparazione in dashboard cucina),
// invisibile come problema finche' qualcuno non se ne accorge per caso.
// render() e' chiamato automaticamente da Laravel.
class TableSessionHasUnservedOrdersException extends Exception
{
    public function __construct()
    {
        parent::__construct('Il tavolo ha ancora ordini non "Servito". Segnali come serviti prima di chiudere il tavolo.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
