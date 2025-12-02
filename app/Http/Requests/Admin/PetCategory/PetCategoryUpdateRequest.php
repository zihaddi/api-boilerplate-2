<?php

namespace App\Http\Requests\Admin\PetCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PetCategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('pet_categories', 'name')->ignore($this->route('id'), 'category_id')
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Category name already exists.',
            'name.max' => 'Category name cannot exceed 100 characters.',
        ];
    }
}
