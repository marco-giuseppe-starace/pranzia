<?php

namespace App\Models;

use App\Enums\AiInteractionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'type',
        'tokens_input',
        'tokens_output',
        'cost_estimate',
    ];

    protected $casts = [
        'type' => AiInteractionType::class,
        'cost_estimate' => 'decimal:4',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TableSession::class, 'session_id');
    }
}
