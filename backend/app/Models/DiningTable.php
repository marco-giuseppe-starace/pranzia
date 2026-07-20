<?php

namespace App\Models;

use App\Enums\TableSessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    // Usata dalla dashboard admin per sapere se il tavolo e' "occupato"
    // (ha una sessione cliente ancora aperta) o "libero".
    //
    // Il vincolo sullo status va passato come constraint di ofMany(), non
    // incatenato con un .where() prima di latestOfMany(): quest'ultima forma
    // calcola il MAX(started_at) su *tutte* le sessioni del tavolo a
    // prescindere dallo stato, e filtra per status solo dopo — quindi se la
    // sessione piu' recente in assoluto e' chiusa, la relazione restituisce
    // null anche quando esiste una sessione piu' vecchia ancora attiva,
    // facendo risultare "libero" un tavolo che in realta' e' occupato.
    public function activeSession(): HasOne
    {
        return $this->hasOne(TableSession::class, 'table_id')
            ->ofMany(
                ['started_at' => 'max'],
                fn ($query) => $query->where('status', TableSessionStatus::Active)
            );
    }
}
