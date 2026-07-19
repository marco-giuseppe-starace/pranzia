<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Lanciata provando a incassare una sessione gia' chiusa (es. doppio
// click, o il tavolo e' stato nel frattempo chiuso da un altro membro
// dello staff). render() e' chiamato automaticamente da Laravel.
class TableSessionNotActiveException extends Exception
{
    public function __construct()
    {
        parent::__construct('Questa sessione non e\' piu\' attiva.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
