<?php

namespace App\Http\Requests\Admin\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0|max:9999.99',
            'estimated_duration' => 'required|integer|min:15|max:480',
            'category' => 'required|in:grooming,bathing,nail_care,dental,specialty,other',
            'requires_pet_categories' => 'nullable|array',
            'requires_pet_categories.*' => 'integer|exists:pet_categories,id',
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
            'organization_id.required' => 'Organization selection is required.',
            'organization_id.exists' => 'Selected organization does not exist.',
            'name.required' => 'Service name is required.',
            'name.max' => 'Service name cannot exceed 255 characters.',
            'base_price.required' => 'Base price is required.',
            'base_price.numeric' => 'Base price must be a number.',
            'base_price.min' => 'Base price cannot be negative.',
            'estimated_duration.required' => 'Estimated duration is required.',
            'estimated_duration.min' => 'Duration must be at least 15 minutes.',
            'estimated_duration.max' => 'Duration cannot exceed 8 hours.',
            'category.required' => 'Service category is required.',
            'category.in' => 'Invalid service category selected.',
        ];
    }
}
