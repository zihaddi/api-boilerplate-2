<?php

namespace App\Http\Requests\Admin\Organization;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
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
            'name.required' => 'Organization name is required.',
            'address.required' => 'Organization address is required.',
            'email.email' => 'Please provide a valid email address.',
            'website.url' => 'Please provide a valid website URL.',
        ];
    }
}
