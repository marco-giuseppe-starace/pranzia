<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Lanciata quando lo staff prova a chiudere (liberare) un tavolo la cui
// sessione attiva non e' ancora stata incassata da "In cassa". Il metodo
// render() qui sotto viene chiamato automaticamente da Laravel: nessun
// try/catch necessario nei controller, la risposta HTTP e' definita
// una volta sola qui.
class TableSessionNotPaidException extends Exception
{
    public function __construct()
    {
        parent::__construct('Il tavolo non e\' stato ancora incassato. Incassalo da "In cassa" prima di chiuderlo.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
