<?php

namespace App\Http\Requests\Admin\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => 'required|exists:pets,id',
            'professional_type' => 'required|in:App\\Models\\GroomerProfile,App\\Models\\DoctorProfile',
            'professional_id' => 'required|integer',
            'service_id' => 'required|exists:services,id',
            'scheduled_datetime' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'location_type' => 'required|in:at_organization,at_customer,mobile',
            'customer_notes' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $professionalType = $this->professional_type;
            $professionalId = $this->professional_id;

            if ($professionalType && $professionalId) {
                // Check if the class exists and the professional exists
                if (class_exists($professionalType)) {
                    $exists = $professionalType::where('id', $professionalId)
                        ->where('status', true) // Only check active professionals
                        ->exists();

                    if (!$exists) {
                        $validator->errors()->add('professional_id', 'The selected professional does not exist or is inactive.');
                    }
                } else {
                    $validator->errors()->add('professional_type', 'Invalid professional type.');
                }
            }
        });
    }
}
