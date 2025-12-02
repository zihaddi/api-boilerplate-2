<?php

namespace App\Http\Requests\Admin\DonationAllocation;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class DonationAllocationUpdateRequest extends FormRequest
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
            'allocated_to_id' => 'sometimes|integer|exists:users,id',
            'amount_allocated' => 'sometimes|numeric|min:1',
            'allocation_date' => 'sometimes|date',
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
            'donation_id.exists' => 'Selected donation does not exist.',
            'allocated_to_id.exists' => 'Selected recipient does not exist.',
            'amount_allocated.min' => 'Allocation amount must be at least 1.',
            'status.in' => 'Status must be one of: pending, approved, completed, cancelled.',
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
