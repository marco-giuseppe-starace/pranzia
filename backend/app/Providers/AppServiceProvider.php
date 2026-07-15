<?php

namespace App\Providers;

use Anthropic\Client as AnthropicClient;
use App\Services\Ai\AnthropicSdkClient;
use App\Services\Ai\ClaudeClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Passiamo la chiave esplicitamente da config invece di lasciare che
        // l'SDK legga getenv(), perche' Laravel non garantisce che le env
        // siano sempre esposte via getenv() (dipende dalla configurazione
        // del repository dotenv).
        $this->app->singleton(ClaudeClient::class, fn () => new AnthropicSdkClient(
            new AnthropicClient(apiKey: config('services.anthropic.key'))
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Limite per sessione tavolo (non per IP): un tavolo condiviso da piu'
        // persone non deve essere bloccato da un limite per-indirizzo.
        RateLimiter::for('ai-per-session', function ($request) {
            return Limit::perMinute(10)->by($request->input('session_id') ?? $request->ip());
        });
    }
}
