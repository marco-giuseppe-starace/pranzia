<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use App\Enums\ServiceRequestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'type', 'note', 'status', 'resolved_at'];

    protected $casts = [
        'type' => ServiceRequestType::class,
        'status' => ServiceRequestStatus::class,
        'resolved_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TableSession::class, 'session_id');
    }
}
