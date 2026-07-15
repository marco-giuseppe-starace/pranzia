<?php

namespace App\Services\Ai;

use Anthropic\Client;

// Implementazione reale: incapsula l'SDK ufficiale Anthropic. La chiave API
// vive solo qui/lato backend, mai esposta al frontend.
class AnthropicSdkClient implements ClaudeClient
{
    public function __construct(private readonly Client $client)
    {
    }

    public function createMessage(string $model, string $system, string $userMessage, int $maxTokens): ClaudeMessage
    {
        $response = $this->client->messages->create(
            maxTokens: $maxTokens,
            model: $model,
            system: $system,
            messages: [
                ['role' => 'user', 'content' => $userMessage],
            ],
        );

        $text = '';
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                $text = $block->text;
                break;
            }
        }

        return new ClaudeMessage(
            text: $text,
            inputTokens: $response->usage->inputTokens,
            outputTokens: $response->usage->outputTokens,
        );
    }
}
