<?php

namespace App\Http\Requests;

use App\Enums\MenuCategoryGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
            'group' => ['sometimes', Rule::enum(MenuCategoryGroup::class)],
        ];
    }
}
