<?php

namespace App\Http\Requests\Admin\ServicePricing;

use Illuminate\Foundation\Http\FormRequest;

class ServicePricingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'location_type' => 'required',
            'price' => 'required|numeric|min:0|max:99999.99',
            'additional_fees' => 'nullable|json',
            'status' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Service is required.',
            'service_id.exists' => 'Selected service does not exist.',
            'location_type.required' => 'Location type is required.',
            'location_type.in' => 'Location type must be home, salon, or mobile.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price cannot exceed 99999.99.',
            'additional_fees.json' => 'Additional fees must be valid JSON.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be active, inactive, or pending.',
        ];
    }
}
