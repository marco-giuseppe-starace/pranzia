<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_count' => ['required', 'integer', 'min:1', 'max:200'],
            'cover_charge' => ['required', 'numeric', 'min:0'],
        ];
    }
}
