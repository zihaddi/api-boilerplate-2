<?php

namespace App\Http\Requests\Admin\MonthlyPayment;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class MonthlyPaymentUpdateRequest extends FormRequest
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
            'program_id' => 'sometimes|integer|exists:monthly_support_programs,id',
            'amount' => 'sometimes|numeric|min:1',
            'payment_date' => 'sometimes|date',
            'due_date' => 'sometimes|date',
            'payment_method' => 'sometimes|string|in:cash,bank_transfer,bkash,nagad,rocket,credit_card,paypal',
            'payment_reference' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|string|in:pending,completed,failed,cancelled',
            'verification_status' => 'sometimes|string|in:pending,verified,rejected',
            'verified_at' => 'sometimes|nullable|date',
            'verified_by' => 'sometimes|nullable|integer|exists:users,id',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'program_id.exists' => 'Selected program does not exist.',
            'amount.min' => 'Payment amount must be at least 1.',
            'verified_by.exists' => 'Selected verifier does not exist.',
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
