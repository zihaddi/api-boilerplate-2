<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => 'required|integer|exists:plans,id',
            'subscription_type' => 'required|string|in:monthly,quarterly,half_yearly,yearly',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'Please select a subscription plan',
            'plan_id.exists' => 'The selected plan is invalid',
            'subscription_type.required' => 'Please select a subscription period',
            'subscription_type.in' => 'Invalid subscription period selected'
        ];
    }
}
