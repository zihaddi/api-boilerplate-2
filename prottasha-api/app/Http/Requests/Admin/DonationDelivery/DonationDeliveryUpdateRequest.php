<?php

namespace App\Http\Requests\Admin\DonationDelivery;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class DonationDeliveryUpdateRequest extends FormRequest
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
            'donation_id' => 'sometimes|integer|exists:donations,id',
            'delivered_to_id' => 'sometimes|integer|exists:users,id',
            'amount_delivered' => 'sometimes|numeric|min:1',
            'delivery_date' => 'sometimes|date',
            'delivery_method' => 'sometimes|string|in:hand_delivery,postal_service,courier,bank_transfer,digital_wallet',
            'tracking_number' => 'sometimes|nullable|string|max:255',
            'delivery_address' => 'sometimes|nullable|string|max:500',
            'notes' => 'sometimes|nullable|string|max:1000',
            'status' => 'sometimes|string|in:pending,in_transit,delivered,failed,returned',
            'verification_code' => 'sometimes|nullable|string|max:20',
            'verification_status' => 'sometimes|string|in:pending,verified,failed',
            'verified_at' => 'sometimes|nullable|date',
            'verified_by' => 'sometimes|nullable|integer|exists:users,id',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'donation_id.exists' => 'Selected donation does not exist.',
            'delivered_to_id.exists' => 'Selected recipient does not exist.',
            'amount_delivered.min' => 'Delivery amount must be at least 1.',
            'delivery_method.in' => 'Delivery method must be one of: hand_delivery, postal_service, courier, bank_transfer, digital_wallet.',
            'status.in' => 'Status must be one of: pending, in_transit, delivered, failed, returned.',
            'verification_status.in' => 'Verification status must be one of: pending, verified, failed.',
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
