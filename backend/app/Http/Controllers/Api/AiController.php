<?php

namespace App\Http\Controllers\Api;

use App\Enums\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiAskRequest;
use App\Http\Requests\AiRecommendRequest;
use App\Models\TableSession;
use App\Services\Ai\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiController extends Controller
{
    public function __construct(private readonly AiAssistantService $ai)
    {
    }

    public function recommend(AiRecommendRequest $request): JsonResponse
    {
        $session = $this->activeSessionOrFail($request->integer('session_id'));
        if ($session instanceof JsonResponse) {
            return $session;
        }

        return $this->safeCall(fn () => $this->ai->recommend($session, $request->input('context')));
    }

    public function ask(AiAskRequest $request): JsonResponse
    {
        $session = $this->activeSessionOrFail($request->integer('session_id'));
        if ($session instanceof JsonResponse) {
            return $session;
        }

        return $this->safeCall(fn () => $this->ai->ask($session, $request->string('question'), $request->input('language')));
    }

    private function activeSessionOrFail(int $sessionId): TableSession|JsonResponse
    {
        $session = TableSession::findOrFail($sessionId);

        if ($session->status !== TableSessionStatus::Active) {
            return response()->json(['message' => 'La sessione non e\' attiva.'], 422);
        }

        return $session;
    }

    // Non lasciamo mai trapelare il messaggio di eccezione grezzo dell'SDK
    // Anthropic (potrebbe contenere dettagli interni) al cliente finale.
    // AiAssistantService::call() ha gia' loggato l'interazione fallita.
    private function safeCall(callable $callback): JsonResponse
    {
        try {
            return response()->json(['text' => $callback()]);
        } catch (Throwable $e) {
            Log::warning('Chiamata Claude API fallita', ['exception' => $e->getMessage()]);

            return response()->json([
                'message' => 'Il servizio IA non e\' al momento disponibile. Riprova tra poco.',
            ], 502);
        }
    }
}
