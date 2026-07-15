<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Chiamato DiningTable (non Table) per evitare ambiguita' col concetto di
// "tabella" nel resto del codice Eloquent; il nome della tabella DB resta
// pero' "tables" come da specifica.
class DiningTable extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = ['qr_token', 'number'];

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class, 'table_id');
    }
}
