<?php

namespace App\Http\Controllers\Api;

use App\Enums\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiAskRequest;
use App\Http\Requests\AiRecommendRequest;
use App\Models\TableSession;
use App\Services\Ai\AiAssistantService;
use Illuminate\Http\JsonResponse;

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

        $text = $this->ai->recommend($session, $request->input('context'));

        return response()->json(['text' => $text]);
    }

    public function ask(AiAskRequest $request): JsonResponse
    {
        $session = $this->activeSessionOrFail($request->integer('session_id'));
        if ($session instanceof JsonResponse) {
            return $session;
        }

        $text = $this->ai->ask($session, $request->string('question'));

        return response()->json(['text' => $text]);
    }

    private function activeSessionOrFail(int $sessionId): TableSession|JsonResponse
    {
        $session = TableSession::findOrFail($sessionId);

        if ($session->status !== TableSessionStatus::Active) {
            return response()->json(['message' => 'La sessione non e\' attiva.'], 422);
        }

        return $session;
    }
}
