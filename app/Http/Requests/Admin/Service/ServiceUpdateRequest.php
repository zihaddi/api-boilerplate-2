<?php

namespace App\Http\Requests\Admin\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id' => 'sometimes|exists:organizations,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'sometimes|numeric|min:0|max:9999.99',
            'estimated_duration' => 'sometimes|integer|min:15|max:480',
            'category' => 'sometimes|in:grooming,bathing,nail_care,dental,specialty,other',
            'requires_pet_categories' => 'nullable|array',
            'requires_pet_categories.*' => 'integer|exists:pet_categories,category_id',
            'status' => 'boolean',
            'pricing' => 'nullable|array',
            'pricing.*.location_type' => 'required_with:pricing|in:in_house,at_organization',
            'pricing.*.price' => 'required_with:pricing|numeric|min:0|max:9999.99',
            'pricing.*.additional_fees' => 'nullable|numeric|min:0|max:999.99',
            'pricing.*.status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.exists' => 'Selected organization does not exist.',
            'name.max' => 'Service name cannot exceed 255 characters.',
            'base_price.numeric' => 'Base price must be a number.',
            'base_price.min' => 'Base price cannot be negative.',
            'estimated_duration.min' => 'Duration must be at least 15 minutes.',
            'estimated_duration.max' => 'Duration cannot exceed 8 hours.',
            'category.in' => 'Invalid service category selected.',
        ];
    }
}
