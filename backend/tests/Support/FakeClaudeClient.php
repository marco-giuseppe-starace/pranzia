<?php

namespace Tests\Support;

use App\Services\Ai\ClaudeClient;
use App\Services\Ai\ClaudeMessage;
use Exception;

// Client Claude finto per i test: nessuna chiamata di rete reale. Registra
// gli argomenti ricevuti cosi' i test possono verificare che il wrapper
// costruisca il prompt e scelga il modello corretti.
class FakeClaudeClient implements ClaudeClient
{
    /** @var array<int, array{model: string, system: string, userMessage: string, maxTokens: int}> */
    public array $calls = [];

    public string $responseText = 'Ti consiglio la bruschetta.';

    public int $responseInputTokens = 100;

    public int $responseOutputTokens = 50;

    public bool $shouldThrow = false;

    public function createMessage(string $model, string $system, string $userMessage, int $maxTokens): ClaudeMessage
    {
        $this->calls[] = compact('model', 'system', 'userMessage', 'maxTokens');

        if ($this->shouldThrow) {
            throw new Exception('Errore simulato Claude API');
        }

        return new ClaudeMessage($this->responseText, $this->responseInputTokens, $this->responseOutputTokens);
    }
}
