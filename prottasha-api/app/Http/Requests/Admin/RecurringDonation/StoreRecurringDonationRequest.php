<?php

namespace App\Http\Requests\Admin\RecurringDonation;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringDonationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'donor_id' => [
                'required',
                'integer',
                'exists:users,id'
            ],
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01'
            ],
            'frequency' => [
                'required',
                'string',
                'in:monthly,quarterly,annually'
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'end_date' => [
                'nullable',
                'date',
                'after:start_date'
            ],
            'payment_method' => [
                'required',
                'string',
                'max:50'
            ],
            'payment_gateway' => [
                'nullable',
                'string',
                'max:50'
            ],
            'gateway_customer_id' => [
                'nullable',
                'string',
                'max:255'
            ],
            'gateway_subscription_id' => [
                'nullable',
                'string',
                'max:255'
            ],
            'status' => [
                'sometimes',
                'string',
                'in:active,paused,cancelled'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'donor_id.required' => 'Donor is required.',
            'donor_id.exists' => 'Selected donor does not exist.',
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'Selected project does not exist.',
            'amount.required' => 'Amount is required.',
            'amount.min' => 'Amount must be greater than 0.',
            'frequency.required' => 'Frequency is required.',
            'frequency.in' => 'Frequency must be monthly, quarterly, or annually.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.after' => 'End date must be after start date.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}
