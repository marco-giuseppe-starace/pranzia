<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiRecommendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'integer', 'exists:table_sessions,id'],
            'context' => ['nullable', 'string', 'max:500'],
        ];
    }
}
