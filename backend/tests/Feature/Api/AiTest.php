<?php

use App\Enums\AiInteractionType;
use App\Enums\TableSessionStatus;
use App\Models\AiInteraction;
use App\Models\Allergen;
use App\Models\MenuItem;
use App\Models\TableSession;
use App\Services\Ai\AiAssistantService;
use App\Services\Ai\ClaudeClient;
use Tests\Support\FakeClaudeClient;

beforeEach(function () {
    $this->fakeClient = new FakeClaudeClient();
    $this->app->instance(ClaudeClient::class, $this->fakeClient);
});

it('returns a recommendation and logs the interaction using the sonnet model', function () {
    $session = TableSession::factory()->create(['language' => 'it']);
    MenuItem::factory()->create(['name' => 'Bruschetta', 'available' => true]);

    $response = $this->postJson('/api/ai/recommend', [
        'session_id' => $session->id,
        'context' => 'vino rosso',
    ]);

    $response->assertOk()->assertJsonPath('text', $this->fakeClient->responseText);

    expect($this->fakeClient->calls)->toHaveCount(1);
    expect($this->fakeClient->calls[0]['model'])->toBe(config('services.anthropic.model_recommend'));
    expect($this->fakeClient->calls[0]['system'])->toContain('Bruschetta')
        ->toContain('Rispondi SOLO sulla base del menu fornito');

    $interaction = AiInteraction::first();
    expect($interaction->session_id)->toBe($session->id)
        ->and($interaction->type)->toBe(AiInteractionType::Recommendation)
        ->and($interaction->tokens_input)->toBe(100)
        ->and($interaction->tokens_output)->toBe(50);
});

it('never sends an empty message to Claude when no context is provided', function () {
    // L'API Claude rifiuta un messaggio utente vuoto (400 "user messages
    // must have non-empty content"): il bottone "Consigliami qualcosa" del
    // frontend non invia alcun context, quindi questo caso va coperto.
    $session = TableSession::factory()->create();
    MenuItem::factory()->create();

    $this->postJson('/api/ai/recommend', ['session_id' => $session->id])->assertOk();

    expect($this->fakeClient->calls[0]['userMessage'])->not->toBe('');
});

it('answers a free question using the haiku model', function () {
    $session = TableSession::factory()->create();

    $response = $this->postJson('/api/ai/ask', [
        'session_id' => $session->id,
        'question' => 'What is in the tiramisu?',
    ]);

    $response->assertOk();
    expect($this->fakeClient->calls[0]['model'])->toBe(config('services.anthropic.model_ask'));

    expect(AiInteraction::first()->type)->toBe(AiInteractionType::Translation);
});

it('uses the language passed by the frontend instead of the session language', function () {
    $session = TableSession::factory()->create(['language' => 'it']);

    $this->postJson('/api/ai/ask', [
        'session_id' => $session->id,
        'question' => 'What is in the tiramisu?',
        'language' => 'en',
    ])->assertOk();

    expect($this->fakeClient->calls[0]['system'])->toContain('(en)')
        ->not->toContain('traducendo nella sua lingua se necessario (it)');
});

it('never sends unavailable menu items to the model', function () {
    $session = TableSession::factory()->create();
    MenuItem::factory()->create(['name' => 'Piatto Disponibile', 'available' => true]);
    MenuItem::factory()->create(['name' => 'Piatto Esaurito', 'available' => false]);

    $this->postJson('/api/ai/recommend', ['session_id' => $session->id])->assertOk();

    $system = $this->fakeClient->calls[0]['system'];
    expect($system)->toContain('Piatto Disponibile')
        ->not->toContain('Piatto Esaurito');
});

it('rejects ai requests for a closed session', function () {
    $session = TableSession::factory()->create(['status' => TableSessionStatus::Closed]);

    $this->postJson('/api/ai/ask', ['session_id' => $session->id, 'question' => 'Ciao'])
        ->assertUnprocessable();

    expect($this->fakeClient->calls)->toBeEmpty();
});

it('validates required fields', function () {
    $this->postJson('/api/ai/ask', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['session_id', 'question']);
});

it('never leaks the raw Claude API exception message to the client', function () {
    $this->fakeClient->shouldThrow = true;
    $session = TableSession::factory()->create();

    $response = $this->postJson('/api/ai/ask', ['session_id' => $session->id, 'question' => 'Ciao']);

    $response->assertStatus(502)
        ->assertJsonPath('message', 'Il servizio IA non e\' al momento disponibile. Riprova tra poco.')
        ->assertJsonMissingPath('text');
    expect($response->getContent())->not->toContain('Errore simulato Claude API');
});

it('logs a zero-cost interaction when the Claude API call fails', function () {
    $this->fakeClient->shouldThrow = true;
    $session = TableSession::factory()->create();

    $service = app(AiAssistantService::class);

    expect(fn () => $service->ask($session, 'Ciao'))->toThrow(Exception::class);

    $interaction = AiInteraction::first();
    expect($interaction->tokens_input)->toBe(0)
        ->and($interaction->tokens_output)->toBe(0)
        ->and((float) $interaction->cost_estimate)->toBe(0.0);
});

it('includes the allergen disclaimer instruction in every system prompt', function () {
    $session = TableSession::factory()->create();
    $item = MenuItem::factory()->create();
    $item->allergens()->attach(Allergen::factory()->create(['name' => 'Glutine']));

    $this->postJson('/api/ai/ask', ['session_id' => $session->id, 'question' => 'Ci sono allergeni?'])
        ->assertOk();

    expect($this->fakeClient->calls[0]['system'])
        ->toContain('Verifica sempre con lo staff in caso di allergie gravi')
        ->toContain('Glutine');
});

it('enforces a rate limit of 10 requests per minute per session', function () {
    $session = TableSession::factory()->create();

    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/ai/ask', ['session_id' => $session->id, 'question' => "Domanda {$i}"])
            ->assertOk();
    }

    $this->postJson('/api/ai/ask', ['session_id' => $session->id, 'question' => 'Domanda 11'])
        ->assertStatus(429);
});
