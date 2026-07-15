<?php

use App\Enums\AiInteractionType;
use App\Models\AiInteraction;
use App\Models\TableSession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects unauthenticated access', function () {
    $this->getJson('/api/admin/ai-costs')->assertUnauthorized();
});

it('reports ai costs grouped by month and type', function () {
    Sanctum::actingAs(User::factory()->create());
    $session = TableSession::factory()->create();

    AiInteraction::create([
        'session_id' => $session->id,
        'type' => AiInteractionType::Recommendation,
        'tokens_input' => 1000,
        'tokens_output' => 500,
        'cost_estimate' => 0.0105,
    ]);
    AiInteraction::create([
        'session_id' => $session->id,
        'type' => AiInteractionType::Translation,
        'tokens_input' => 2000,
        'tokens_output' => 800,
        'cost_estimate' => 0.006,
    ]);

    $response = $this->getJson('/api/admin/ai-costs');

    $response->assertOk()->assertJsonCount(2, 'data');
});
