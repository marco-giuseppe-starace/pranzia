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

        return response()->json($this->currentSettings());
    }

    private function currentSettings(): array
    {
        return [
            'table_count' => (int) Setting::get('table_count', self::DEFAULTS['table_count']),
            'cover_charge' => Setting::get('cover_charge', self::DEFAULTS['cover_charge']),
        ];
    }
}
