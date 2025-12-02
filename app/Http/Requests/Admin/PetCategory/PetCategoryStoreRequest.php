<?php

namespace App\Http\Requests\Admin\PetCategory;

use Illuminate\Foundation\Http\FormRequest;

class PetCategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:pet_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'Category name already exists.',
            'name.max' => 'Category name cannot exceed 100 characters.',
        ];
    }
}
