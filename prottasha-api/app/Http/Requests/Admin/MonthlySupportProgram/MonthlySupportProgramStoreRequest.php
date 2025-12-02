<?php

namespace App\Http\Requests\Admin\MonthlySupportProgram;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class MonthlySupportProgramStoreRequest extends FormRequest
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
            'donor_id' => 'required|integer|exists:users,id',
            'beneficiary_id' => 'required|integer|exists:users,id',
            'monthly_amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3|in:BDT,USD,EUR',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'payment_day' => 'required|integer|min:1|max:28',
            'status' => 'required|string|in:active,paused,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'donor_id.required' => 'Donor is required.',
            'donor_id.exists' => 'Selected donor does not exist.',
            'beneficiary_id.required' => 'Beneficiary is required.',
            'beneficiary_id.exists' => 'Selected beneficiary does not exist.',
            'monthly_amount.required' => 'Monthly amount is required.',
            'monthly_amount.min' => 'Monthly amount must be at least 1.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Currency must be one of: BDT, USD, EUR.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.after' => 'End date must be after start date.',
            'payment_day.required' => 'Payment day is required.',
            'payment_day.min' => 'Payment day must be between 1 and 28.',
            'payment_day.max' => 'Payment day must be between 1 and 28.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: active, paused, completed, cancelled.',
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
