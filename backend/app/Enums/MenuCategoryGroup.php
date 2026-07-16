<?php

namespace App\Enums;

// Le 3 macro-sezioni verticali del menu (ognuna scorre poi in orizzontale
// al suo interno, categoria per categoria): Cibo (antipasti/primi/secondi),
// Bevande (bevande/vini/ecc.), Dolci.
enum MenuCategoryGroup: string
{
    case Food = 'food';
    case Drink = 'drink';
    case Dessert = 'dessert';
}
