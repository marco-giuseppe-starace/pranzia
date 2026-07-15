<?php

namespace App\Services\Ai;

use App\Enums\AiInteractionType;
use App\Models\AiInteraction;
use App\Models\MenuCategory;
use App\Models\TableSession;
use Throwable;

class AiAssistantService
{
    // Prezzi pubblici Anthropic in USD per milione di token (input/output),
    // usati solo per stimare il costo da loggare in ai_interactions.
    private const PRICING = [
        'claude-haiku-4-5' => ['input' => 1.00, 'output' => 5.00],
        'claude-sonnet-4-6' => ['input' => 3.00, 'output' => 15.00],
    ];

    public function __construct(
        private readonly ClaudeClient $client,
        private readonly SystemPromptBuilder $promptBuilder,
    ) {
    }

    public function recommend(TableSession $session, ?string $context): string
    {
        $model = config('services.anthropic.model_recommend');
        $task = 'Suggerisci 1-2 piatti in abbinamento o upselling, basati sul contesto del cliente'
            .($context ? " (\"{$context}\")" : '').'.';

        return $this->call($session, AiInteractionType::Recommendation, $model, $task, $context ?? '');
    }

    public function ask(TableSession $session, string $question, ?string $language = null): string
    {
        $model = config('services.anthropic.model_ask');
        // $language e' un hint dal frontend (lingua correntemente selezionata
        // nell'interfaccia), usato al posto di $session->language che resta
        // quella impostata all'avvio sessione e non viene mai aggiornata.
        $targetLanguage = $language ?? $session->language;
        $task = "Rispondi alla domanda del cliente, traducendo nella sua lingua se necessario ({$targetLanguage}).";

        return $this->call($session, AiInteractionType::Translation, $model, $task, $question);
    }

    private function call(
        TableSession $session,
        AiInteractionType $type,
        string $model,
        string $task,
        string $userMessage,
    ): string {
        $categories = MenuCategory::with('menuItems.allergens')
            ->orderBy('sort_order')
            ->get()
            ->map(function (MenuCategory $category) {
                $category->setRelation(
                    'menuItems',
                    $category->menuItems->where('available', true)->values()
                );

                return $category;
            });

        $system = $this->promptBuilder->build($task, $categories);

        try {
            $result = $this->client->createMessage($model, $system, $userMessage, 1024);
        } catch (Throwable $e) {
            // Anche una chiamata fallita va tracciata (costo 0), cosi' il
            // report /api/admin/ai-costs riflette anche i tentativi falliti.
            $this->log($session, $type, 0, 0, $model);

            throw $e;
        }

        $this->log($session, $type, $result->inputTokens, $result->outputTokens, $model);

        return $result->text;
    }

    private function log(TableSession $session, AiInteractionType $type, int $inputTokens, int $outputTokens, string $model): void
    {
        AiInteraction::create([
            'session_id' => $session->id,
            'type' => $type,
            'tokens_input' => $inputTokens,
            'tokens_output' => $outputTokens,
            'cost_estimate' => $this->estimateCost($model, $inputTokens, $outputTokens),
        ]);
    }

    private function estimateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::PRICING[$model] ?? ['input' => 0.0, 'output' => 0.0];

        return round(
            ($inputTokens / 1_000_000) * $pricing['input']
            + ($outputTokens / 1_000_000) * $pricing['output'],
            4
        );
    }
}
