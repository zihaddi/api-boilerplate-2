<?php

namespace App\Http\Requests\Admin\MonthlySupportProgram;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class MonthlySupportProgramUpdateRequest extends FormRequest
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
            'beneficiary_id' => 'sometimes|integer|exists:users,id',
            'monthly_amount' => 'sometimes|numeric|min:1',
            'currency' => 'sometimes|string|max:3|in:BDT,USD,EUR',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|nullable|date|after:start_date',
            'payment_day' => 'sometimes|integer|min:1|max:28',
            'status' => 'sometimes|string|in:active,paused,completed,cancelled',
            'total_paid' => 'sometimes|numeric|min:0',
            'next_payment_date' => 'sometimes|nullable|date',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'donor_id.exists' => 'Selected donor does not exist.',
            'beneficiary_id.exists' => 'Selected beneficiary does not exist.',
            'monthly_amount.min' => 'Monthly amount must be at least 1.',
            'currency.in' => 'Currency must be one of: BDT, USD, EUR.',
            'end_date.after' => 'End date must be after start date.',
            'payment_day.min' => 'Payment day must be between 1 and 28.',
            'payment_day.max' => 'Payment day must be between 1 and 28.',
            'status.in' => 'Status must be one of: active, paused, completed, cancelled.',
            'total_paid.min' => 'Total paid amount cannot be negative.',
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
