<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Lanciata se il cliente prova ad aprire/scaricare/inviare la ricevuta
// prima che lo staff abbia incassato il tavolo. render() e' chiamato
// automaticamente da Laravel.
class ReceiptNotAvailableException extends Exception
{
    public function __construct()
    {
        parent::__construct('La ricevuta sara\' disponibile dopo che lo staff avra\' incassato il tavolo.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 403);
    }
}
