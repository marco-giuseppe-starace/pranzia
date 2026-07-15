<?php

namespace App\Enums;

// Stato di avanzamento di un ordine, tracciato dalla dashboard cucina.
enum OrderStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case Served = 'served';
}
