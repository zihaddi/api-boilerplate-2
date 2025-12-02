<?php

namespace App\Http\Requests\Admin\PetSubcategory;

use Illuminate\Foundation\Http\FormRequest;

class PetSubcategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:pet_categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'size_category' => 'nullable|in:extra_small,small,medium,large,extra_large',
            'typical_grooming_frequency' => 'nullable|in:weekly,bi_weekly,monthly,quarterly,as_needed',
            'special_care_requirements' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Pet category is required.',
            'category_id.exists' => 'Selected pet category does not exist.',
            'name.required' => 'Subcategory name is required.',
            'name.max' => 'Subcategory name cannot exceed 100 characters.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'size_category.in' => 'Size category must be extra_small, small, medium, large, or extra_large.',
            'typical_grooming_frequency.in' => 'Grooming frequency must be weekly, bi_weekly, monthly, quarterly, or as_needed.',
            'special_care_requirements.max' => 'Special care requirements cannot exceed 1000 characters.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if subcategory name is unique within the same category
            $exists = \App\Models\PetSubcategory::where('category_id', $this->category_id)
                ->where('name', $this->name)
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'This subcategory name already exists in the selected category.');
            }
        });
    }
}
