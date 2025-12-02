<?php

namespace App\Http\Requests\Admin\Donation;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class DonationUpdateRequest extends FormRequest
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
            'project_id' => 'sometimes|nullable|integer|exists:projects,id',
            'donation_taker_id' => 'sometimes|nullable|integer|exists:users,id',
            'amount' => 'sometimes|numeric|min:1',
            'currency' => 'sometimes|string|max:3|in:BDT,USD,EUR',
            'donation_type' => 'sometimes|string|in:monetary,goods,service',
            'payment_method' => 'sometimes|string|in:cash,bank_transfer,bkash,nagad,rocket,credit_card,paypal',
            'payment_status' => 'sometimes|string|in:pending,completed,failed,refunded',
            'payment_reference' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string|max:1000',
            'status' => 'sometimes|string|in:pending,approved,completed,cancelled',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'donor_id.exists' => 'Selected donor does not exist.',
            'amount.min' => 'Donation amount must be at least 1.',
            'currency.in' => 'Currency must be one of: BDT, USD, EUR.',
            'donation_type.in' => 'Donation type must be one of: monetary, goods, service.',
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
