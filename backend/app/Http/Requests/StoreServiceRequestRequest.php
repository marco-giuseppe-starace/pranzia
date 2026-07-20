<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ServiceRequestType;

class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'integer', 'exists:table_sessions,id'],
            'type' => ['required', new Enum(ServiceRequestType::class)],
            // Facoltativa per i tipi comuni, ma e' li' che il cliente
            // specifica cosa intende quando sceglie "Altro".
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
