<?php

namespace App\Http\Requests\Admin\GroomerProfile;

use Illuminate\Foundation\Http\FormRequest;

class GroomerProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|max:255',
            'experience_years' => 'required|integer|min:0|max:50',
            'hourly_rate' => 'nullable|numeric|min:0|max:9999.99',
            'bio' => 'nullable|string|max:1000',
            'status' => 'boolean',
            'joined_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User selection is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'organization_id.required' => 'Organization selection is required.',
            'organization_id.exists' => 'Selected organization does not exist.',
            'experience_years.required' => 'Experience years is required.',
            'experience_years.integer' => 'Experience years must be a number.',
            'experience_years.min' => 'Experience years cannot be negative.',
            'experience_years.max' => 'Experience years cannot exceed 50.',
            'hourly_rate.numeric' => 'Hourly rate must be a number.',
            'hourly_rate.min' => 'Hourly rate cannot be negative.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',
        ];
    }
}
