<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    // Valori di default finche' lo staff non le imposta esplicitamente
    // (coerenti coi 10 tavoli demo seedati da TableSeeder).
    private const DEFAULTS = [
        'table_count' => '10',
        'cover_charge' => '0',
    ];

    public function index(): JsonResponse
    {
        return response()->json($this->currentSettings());
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        Setting::set('table_count', (string) $request->integer('table_count'));
        Setting::set('cover_charge', (string) $request->float('cover_charge'));

        // Campo lasciato vuoto = "non toccare la chiave gia' salvata": cosi'
        // aggiornare le altre impostazioni non la cancella per sbaglio, e
        // non serve reinserirla ogni volta che si cambia il numero tavoli.
        if ($request->filled('anthropic_api_key')) {
            Setting::set('anthropic_api_key', trim($request->string('anthropic_api_key')));
        }

        return response()->json($this->currentSettings());
    }

    private function currentSettings(): array
    {
        return [
            'table_count' => (int) Setting::get('table_count', self::DEFAULTS['table_count']),
            'cover_charge' => Setting::get('cover_charge', self::DEFAULTS['cover_charge']),
            'anthropic_api_key_masked' => $this->maskApiKey(Setting::get('anthropic_api_key')),
        ];
    }

    // Mostra solo le prime 7 lettere, il resto camuffato con asterischi:
    // la chiave completa non deve mai piu' tornare al frontend una volta
    // salvata.
    private function maskApiKey(?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        $visible = substr($key, 0, 7);

        return $visible.str_repeat('*', max(strlen($key) - strlen($visible), 8));
    }
}
