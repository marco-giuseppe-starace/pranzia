<?php

namespace App\Enums;

// Tipo di chiamata IA loggata in ai_interactions, per il report costi.
enum AiInteractionType: string
{
    case Recommendation = 'recommendation';
    case Translation = 'translation';
    case AllergenCheck = 'allergen_check';
}
