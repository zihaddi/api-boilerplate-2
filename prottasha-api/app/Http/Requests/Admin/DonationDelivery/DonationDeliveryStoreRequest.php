<?php

namespace App\Http\Requests\Admin\DonationDelivery;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class DonationDeliveryStoreRequest extends FormRequest
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
            'donation_id' => 'required|integer|exists:donations,id',
            'delivered_to_id' => 'required|integer|exists:users,id',
            'amount_delivered' => 'required|numeric|min:1',
            'delivery_date' => 'required|date',
            'delivery_method' => 'required|string|in:hand_delivery,postal_service,courier,bank_transfer,digital_wallet',
            'tracking_number' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:pending,in_transit,delivered,failed,returned',
            'verification_code' => 'nullable|string|max:20',
            'verification_status' => 'required|string|in:pending,verified,failed',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'donation_id.required' => 'Donation is required.',
            'donation_id.exists' => 'Selected donation does not exist.',
            'delivered_to_id.required' => 'Delivery recipient is required.',
            'delivered_to_id.exists' => 'Selected recipient does not exist.',
            'amount_delivered.required' => 'Delivery amount is required.',
            'amount_delivered.min' => 'Delivery amount must be at least 1.',
            'delivery_date.required' => 'Delivery date is required.',
            'delivery_method.required' => 'Delivery method is required.',
            'delivery_method.in' => 'Delivery method must be one of: hand_delivery, postal_service, courier, bank_transfer, digital_wallet.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: pending, in_transit, delivered, failed, returned.',
            'verification_status.required' => 'Verification status is required.',
            'verification_status.in' => 'Verification status must be one of: pending, verified, failed.',
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
