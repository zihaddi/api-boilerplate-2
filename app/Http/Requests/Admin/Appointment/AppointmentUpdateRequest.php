<?php

namespace App\Http\Requests\Admin\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => 'sometimes|exists:pets,id',
            'groomer_profile_id' => 'sometimes|exists:groomer_profiles,id',
            'service_id' => 'sometimes|exists:services,id',
            'scheduled_datetime' => 'sometimes|date|after:now',
            'duration_minutes' => 'sometimes|integer|min:15|max:480',
            'location_type' => 'sometimes|in:in_house,at_organization',
            'status' => 'sometimes|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'customer_notes' => 'nullable|string|max:1000',
            'groomer_notes' => 'nullable|string|max:1000',
            'cancellation_reason' => 'nullable|string|max:500',
            'base_cost' => 'sometimes|numeric|min:0|max:9999.99',
            'additional_fees' => 'nullable|numeric|min:0|max:999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.exists' => 'Selected pet does not exist.',
            'groomer_profile_id.exists' => 'Selected groomer does not exist.',
            'service_id.exists' => 'Selected service does not exist.',
            'scheduled_datetime.date' => 'Please provide a valid date and time.',
            'scheduled_datetime.after' => 'Appointment must be scheduled for a future date and time.',
            'duration_minutes.integer' => 'Duration must be a number.',
            'duration_minutes.min' => 'Duration must be at least 15 minutes.',
            'duration_minutes.max' => 'Duration cannot exceed 8 hours.',
            'location_type.in' => 'Location type must be either in-house or at-organization.',
            'status.in' => 'Invalid appointment status.',
            'customer_notes.max' => 'Customer notes cannot exceed 1000 characters.',
            'groomer_notes.max' => 'Groomer notes cannot exceed 1000 characters.',
            'cancellation_reason.max' => 'Cancellation reason cannot exceed 500 characters.',
            'base_cost.numeric' => 'Base cost must be a number.',
            'base_cost.min' => 'Base cost cannot be negative.',
            'base_cost.max' => 'Base cost cannot exceed 9999.99.',
            'additional_fees.numeric' => 'Additional fees must be a number.',
            'additional_fees.min' => 'Additional fees cannot be negative.',
            'additional_fees.max' => 'Additional fees cannot exceed 999.99.',
        ];
    }

    /**
     * Custom validation for business rules
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // If status is being changed to cancelled, cancellation_reason should be provided
            if ($this->has('status') && $this->status === 'cancelled') {
                if (!$this->has('cancellation_reason') || empty($this->cancellation_reason)) {
                    $validator->errors()->add('cancellation_reason', 'Cancellation reason is required when cancelling an appointment.');
                }
            }

            // If appointment is completed or in progress, don't allow datetime changes
            if ($this->has('scheduled_datetime')) {
                $appointment = request()->route('id');
                if ($appointment) {
                    // This would need the actual appointment model to check current status
                    // You might want to implement this check in the repository instead
                }
            }
        });
    }
}
