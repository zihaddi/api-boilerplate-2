<?php

namespace App\Http\Requests\Admin\MonthlyPayment;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class MonthlyPaymentStoreRequest extends FormRequest
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
            'program_id' => 'required|integer|exists:monthly_support_programs,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:today',
            'payment_method' => 'required|string|in:cash,bank_transfer,bkash,nagad,rocket,credit_card,paypal',
            'payment_reference' => 'nullable|string|max:255',
            'status' => 'required|string|in:pending,completed,failed,cancelled',
            'verification_status' => 'required|string|in:pending,verified,rejected',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'program_id.required' => 'Monthly support program is required.',
            'program_id.exists' => 'Selected program does not exist.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be at least 1.',
            'payment_date.required' => 'Payment date is required.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be today or later.',
            'payment_method.required' => 'Payment method is required.',
            'status.required' => 'Payment status is required.',
            'verification_status.required' => 'Verification status is required.',
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
