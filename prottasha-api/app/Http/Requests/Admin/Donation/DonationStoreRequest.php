<?php

namespace App\Http\Requests\Admin\Donation;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class DonationStoreRequest extends FormRequest
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
            'project_id' => 'nullable|integer|exists:projects,id',
            'donation_taker_id' => 'nullable|integer|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3|in:BDT,USD,EUR',
            'donation_type' => 'required|string|in:monetary,goods,service',
            'payment_method' => 'required|string|in:cash,bank_transfer,bkash,nagad,rocket,credit_card,paypal',
            'payment_status' => 'required|string|in:pending,completed,failed,refunded',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:pending,approved,completed,cancelled',
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
            'amount.required' => 'Donation amount is required.',
            'amount.min' => 'Donation amount must be at least 1.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Currency must be one of: BDT, USD, EUR.',
            'donation_type.required' => 'Donation type is required.',
            'donation_type.in' => 'Donation type must be one of: monetary, goods, service.',
            'payment_method.required' => 'Payment method is required.',
            'payment_status.required' => 'Payment status is required.',
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
