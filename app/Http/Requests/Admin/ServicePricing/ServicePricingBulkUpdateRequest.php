<?php

namespace App\Http\Requests\Admin\ServicePricing;

use Illuminate\Foundation\Http\FormRequest;

class ServicePricingBulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pricings' => 'required|array|min:1',
            'pricings.*.id' => 'sometimes|exists:service_pricings,id',
            'pricings.*.service_id' => 'required|exists:services,id',
            'pricings.*.location_type' => 'required',
            'pricings.*.price' => 'required|numeric|min:0|max:99999.99',
            'pricings.*.additional_fees' => 'nullable|json',
            'pricings.*.status' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'pricings.required' => 'At least one pricing entry is required.',
            'pricings.array' => 'Pricing data must be an array.',
            'pricings.min' => 'At least one pricing entry is required.',
            'pricings.*.service_id.required' => 'Service is required for each pricing entry.',
            'pricings.*.service_id.exists' => 'Selected service does not exist.',
            'pricings.*.location_type.required' => 'Location type is required for each pricing entry.',
            'pricings.*.location_type.in' => 'Location type must be home, salon, or mobile.',
            'pricings.*.price.required' => 'Price is required for each pricing entry.',
            'pricings.*.price.numeric' => 'Price must be a number.',
            'pricings.*.price.min' => 'Price cannot be negative.',
            'pricings.*.price.max' => 'Price cannot exceed 99999.99.',
            'pricings.*.additional_fees.json' => 'Additional fees must be valid JSON.',
            'pricings.*.status.required' => 'Status is required for each pricing entry.',
            'pricings.*.status.in' => 'Status must be active, inactive, or pending.',
        ];
    }
}
