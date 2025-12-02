<?php

namespace App\Http\Requests\Admin\UserProfile;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UserProfileStoreRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id|unique:user_profiles,user_id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country_id' => 'nullable|integer|exists:countries,id',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'thana_id' => 'nullable|integer|exists:thanas,id',
            'upazila_id' => 'nullable|integer|exists:upazilas,id',
            'union_id' => 'nullable|integer|exists:unions,id',
            'postal_code' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'education' => 'nullable|string|max:100',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'profile_picture' => 'nullable|string|max:500',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'verification_documents' => 'nullable|json',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'user_id.unique' => 'User profile already exists for this user.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'date_of_birth.before' => 'Date of birth must be before today.',
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
