<?php

namespace App\Http\Requests\Admin\Pet;

use Illuminate\Foundation\Http\FormRequest;

class PetStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:100',
            'category_id' => 'required|exists:pet_categories,id',
            'subcategory_id' => 'required|exists:pet_subcategories,id',
            'breed_id' => 'required|exists:pet_breeds,id',
            'birthday' => 'nullable|date|before:today',
            'weight' => 'nullable|numeric|min:0.01|max:999.99',
            'sex' => 'required|in:male,female,unknown',
            'current_medications' => 'nullable|array',
            'current_medications.*' => 'string|max:255',
            'medication_allergies' => 'nullable|array',
            'medication_allergies.*' => 'string|max:255',
            'health_conditions' => 'nullable|array',
            'health_conditions.*' => 'string|max:255',
            'special_notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|string|max:255',
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'owner_id.required' => 'Pet owner is required.',
            'owner_id.exists' => 'Selected pet owner does not exist.',
            'name.required' => 'Pet name is required.',
            'name.max' => 'Pet name cannot exceed 100 characters.',
            'category_id.required' => 'Pet category is required.',
            'category_id.exists' => 'Selected pet category does not exist.',
            'subcategory_id.required' => 'Pet subcategory is required.',
            'subcategory_id.exists' => 'Selected pet subcategory does not exist.',
            'breed_id.required' => 'Pet breed is required.',
            'breed_id.exists' => 'Selected pet breed does not exist.',
            'birthday.date' => 'Birthday must be a valid date.',
            'birthday.before' => 'Birthday must be before today.',
            'weight.numeric' => 'Weight must be a number.',
            'weight.min' => 'Weight must be at least 0.01 kg.',
            'weight.max' => 'Weight cannot exceed 999.99 kg.',
            'sex.required' => 'Pet sex is required.',
            'sex.in' => 'Pet sex must be male, female, or unknown.',
        ];
    }
}
