<?php

namespace App\Http\Requests\Admin\PetBreed;

use Illuminate\Foundation\Http\FormRequest;

class PetBreedUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subcategory_id' => 'sometimes|exists:pet_subcategories,subcategory_id',
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:500',
            'typical_weight_min' => 'nullable|numeric|min:0|max:999.99',
            'typical_weight_max' => 'nullable|numeric|min:0|max:999.99|gte:typical_weight_min',
            'typical_height_min' => 'nullable|numeric|min:0|max:999.99',
            'typical_height_max' => 'nullable|numeric|min:0|max:999.99|gte:typical_height_min',
            'life_expectancy_min' => 'nullable|integer|min:1|max:50',
            'life_expectancy_max' => 'nullable|integer|min:1|max:50|gte:life_expectancy_min',
            'temperament' => 'nullable|string|max:500',
            'grooming_requirements' => 'nullable|string|max:500',
            'exercise_needs' => 'nullable|in:low,moderate,high',
            'good_with_children' => 'nullable|boolean',
            'good_with_pets' => 'nullable|boolean',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'subcategory_id.exists' => 'Selected pet subcategory does not exist.',
            'name.max' => 'Breed name cannot exceed 100 characters.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'typical_weight_min.numeric' => 'Minimum weight must be a number.',
            'typical_weight_min.min' => 'Minimum weight cannot be negative.',
            'typical_weight_min.max' => 'Minimum weight cannot exceed 999.99 kg.',
            'typical_weight_max.numeric' => 'Maximum weight must be a number.',
            'typical_weight_max.min' => 'Maximum weight cannot be negative.',
            'typical_weight_max.max' => 'Maximum weight cannot exceed 999.99 kg.',
            'typical_weight_max.gte' => 'Maximum weight must be greater than or equal to minimum weight.',
            'typical_height_min.numeric' => 'Minimum height must be a number.',
            'typical_height_min.min' => 'Minimum height cannot be negative.',
            'typical_height_min.max' => 'Minimum height cannot exceed 999.99 cm.',
            'typical_height_max.numeric' => 'Maximum height must be a number.',
            'typical_height_max.min' => 'Maximum height cannot be negative.',
            'typical_height_max.max' => 'Maximum height cannot exceed 999.99 cm.',
            'typical_height_max.gte' => 'Maximum height must be greater than or equal to minimum height.',
            'life_expectancy_min.integer' => 'Minimum life expectancy must be a whole number.',
            'life_expectancy_min.min' => 'Minimum life expectancy must be at least 1 year.',
            'life_expectancy_min.max' => 'Minimum life expectancy cannot exceed 50 years.',
            'life_expectancy_max.integer' => 'Maximum life expectancy must be a whole number.',
            'life_expectancy_max.min' => 'Maximum life expectancy must be at least 1 year.',
            'life_expectancy_max.max' => 'Maximum life expectancy cannot exceed 50 years.',
            'life_expectancy_max.gte' => 'Maximum life expectancy must be greater than or equal to minimum life expectancy.',
            'temperament.max' => 'Temperament description cannot exceed 500 characters.',
            'grooming_requirements.max' => 'Grooming requirements cannot exceed 500 characters.',
            'exercise_needs.in' => 'Exercise needs must be low, moderate, or high.',
            'good_with_children.boolean' => 'Good with children must be true or false.',
            'good_with_pets.boolean' => 'Good with pets must be true or false.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if breed name is unique within the same subcategory (excluding current record)
            if ($this->has('name') && $this->has('subcategory_id')) {
                $exists = \App\Models\PetBreed::where('subcategory_id', $this->subcategory_id)
                    ->where('name', $this->name)
                    ->where('breed_id', '!=', $this->route('id'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', 'This breed name already exists in the selected subcategory.');
                }
            }
        });
    }
}
