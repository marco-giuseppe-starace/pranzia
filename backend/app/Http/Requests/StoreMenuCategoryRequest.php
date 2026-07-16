<?php

namespace App\Http\Requests;

use App\Enums\MenuCategoryGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
            'group' => ['required', Rule::enum(MenuCategoryGroup::class)],
        ];
    }
}
