<?php

namespace App\Models;

use App\Enums\TableSessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableSession extends Model
{
    use HasFactory;

    protected $fillable = ['table_id', 'language', 'status', 'started_at'];

    protected $casts = [
        'status' => TableSessionStatus::class,
        'started_at' => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'session_id');
    }

    public function aiInteractions(): HasMany
    {
        return $this->hasMany(AiInteraction::class, 'session_id');
    }
}
