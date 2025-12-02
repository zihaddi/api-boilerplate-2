<?php

namespace App\Http\Requests\Admin\Organization;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'is_default' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Organization name must be a string.',
            'name.max' => 'Organization name cannot exceed 255 characters.',
            'address.string' => 'Organization address must be a string.',
            'phone.string' => 'Phone number must be a string.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'website.url' => 'Please provide a valid website URL.',
            'website.max' => 'Website URL cannot exceed 255 characters.',
            'is_default.boolean' => 'Is default must be true or false.',
            'status.boolean' => 'Status must be true or false.',
        ];
    }
}
