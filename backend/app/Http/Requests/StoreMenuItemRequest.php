<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'available' => ['boolean'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'allergen_ids' => ['array'],
            'allergen_ids.*' => ['integer', 'exists:allergens,id'],
        ];
    }
}
