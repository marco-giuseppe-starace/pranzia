<?php

namespace App\Services\Ai;

// Astrazione sopra l'SDK Anthropic: permette di bindare un'implementazione
// finta nei test Pest senza fare chiamate di rete reali.
interface ClaudeClient
{
    public function createMessage(string $model, string $system, string $userMessage, int $maxTokens): ClaudeMessage;
}
