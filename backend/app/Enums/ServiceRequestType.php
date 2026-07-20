<?php

namespace App\Enums;

// Richieste rapide dal tavolo ("Mi puo' portare..."), senza passare dal
// menu/ordine. "Other" copre tutto cio' che non rientra in queste
// categorie comuni, con dettaglio libero nel campo note. Niente "acqua":
// e' gia' un piatto ordinabile dal menu.
enum ServiceRequestType: string
{
    case Glass = 'glass';
    case Salt = 'salt';
    case Oil = 'oil';
    case Napkins = 'napkins';
    case Cutlery = 'cutlery';
    case Bill = 'bill';
    case Other = 'other';
}
