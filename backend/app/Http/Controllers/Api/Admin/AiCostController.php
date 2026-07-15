<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiInteraction;
use Illuminate\Http\JsonResponse;

class AiCostController extends Controller
{
    // Report spesa IA mensile per il ristoratore/sviluppatore, raggruppato
    // per mese e tipo di interazione. Raggruppato in PHP (non con funzioni
    // SQL specifiche di un DB, es. DATE_FORMAT) per restare portabile tra
    // MySQL (produzione) e SQLite (test).
    public function index(): JsonResponse
    {
        $rows = AiInteraction::query()
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn (AiInteraction $interaction) => $interaction->created_at->format('Y-m').'|'.$interaction->type->value)
            ->map(function ($group) {
                /** @var AiInteraction $first */
                $first = $group->first();

                return [
                    'month' => $first->created_at->format('Y-m'),
                    'type' => $first->type->value,
                    'tokens_input' => $group->sum('tokens_input'),
                    'tokens_output' => $group->sum('tokens_output'),
                    'cost_estimate' => round((float) $group->sum('cost_estimate'), 4),
                    'interactions' => $group->count(),
                ];
            })
            ->sortByDesc('month')
            ->values();

        return response()->json(['data' => $rows]);
    }
}
