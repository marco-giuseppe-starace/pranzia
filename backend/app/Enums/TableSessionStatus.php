<?php

namespace App\Enums;

// Stato di una sessione cliente aperta da un tavolo tramite QR code.
enum TableSessionStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
}
