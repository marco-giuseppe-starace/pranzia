<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiAskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'integer', 'exists:table_sessions,id'],
            'question' => ['required', 'string', 'max:500'],
            // Lingua correntemente selezionata nell'interfaccia: se presente,
            // sovrascrive la lingua di sessione solo per questa chiamata IA.
            'language' => ['nullable', 'string', 'max:5'],
        ];
    }
}
