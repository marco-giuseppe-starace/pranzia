<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionGuestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ne' 0 (deve esserci almeno una persona) ne' un numero assurdo
        // (limite di sicurezza contro input a casaccio, non un vincolo
        // realistico sulla capienza del locale).
        return [
            'guests' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
