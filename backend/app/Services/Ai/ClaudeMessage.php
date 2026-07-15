<?php

namespace App\Services\Ai;

// Risultato normalizzato di una chiamata a Claude, indipendente dai tipi
// interni dell'SDK - permette di iniettare un fake nei test senza
// dipendere dalle classi Anthropic\Messages\*.
readonly class ClaudeMessage
{
    public function __construct(
        public string $text,
        public int $inputTokens,
        public int $outputTokens,
    ) {
    }
}
