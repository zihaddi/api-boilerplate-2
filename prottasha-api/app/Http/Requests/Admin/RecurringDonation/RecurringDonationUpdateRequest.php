<?php

namespace App\Http\Requests\Admin\RecurringDonation;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class RecurringDonationUpdateRequest extends FormRequest
{
    use HttpResponses;

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
            'donor_id' => 'sometimes|integer|exists:users,id',
            'project_id' => 'sometimes|integer|exists:projects,id',
            'amount' => 'sometimes|numeric|min:0.01',
            'frequency' => 'sometimes|string|in:monthly,quarterly,annually',
            'start_date' => 'sometimes|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'payment_method' => 'sometimes|string|max:50',
            'payment_gateway' => 'nullable|string|max:50',
            'gateway_customer_id' => 'nullable|string|max:255',
            'gateway_subscription_id' => 'nullable|string|max:255',
            'status' => 'sometimes|string|in:active,paused,cancelled,completed',
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
            'donor_id.exists' => 'Selected donor does not exist.',
            'project_id.exists' => 'Selected project does not exist.',
            'amount.min' => 'Amount must be greater than 0.',
            'frequency.in' => 'Frequency must be monthly, quarterly, or annually.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.after' => 'End date must be after start date.',
            'status.in' => 'Status must be active, paused, cancelled, or completed.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->error(
                $validator->errors(),
                ValidationConstants::ERROR,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false
            )
        );
    }
}
