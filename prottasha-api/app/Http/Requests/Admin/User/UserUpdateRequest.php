<?php

namespace App\Http\Requests\Admin\User;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UserUpdateRequest extends FormRequest
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
            'mobile' => 'sometimes|string|max:15|unique:users,mobile,' . $this->route('user'),
            'ccode' => 'sometimes|string|max:5',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $this->route('user'),
            'password' => 'nullable|string|min:8|confirmed',
            'auth_code' => 'nullable|string|max:10',
            'is_verify' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
            'user_type' => 'sometimes|integer',
            'photo' => 'nullable|string',

            // UserInfo data validation
            'first_name' => 'sometimes|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'dob' => 'sometimes|date',
            'religion_id' => 'nullable|integer',
            'gender' => 'sometimes|integer',
            'occupation' => 'nullable|string|max:255',
            'nationality_id' => 'nullable|integer',
            'vulnerability_info' => 'nullable|string|max:255',
            'pre_country' => 'sometimes|integer',
            'pre_srteet_address' => 'sometimes|string|max:255',
            'pre_city' => 'sometimes|string|max:255',
            'pre_provience' => 'sometimes|string|max:255',
            'pre_zip' => 'sometimes|string|max:10',
            'same_as_present_address' => 'sometimes|boolean',
            'per_country' => 'sometimes|integer',
            'per_srteet_address' => 'sometimes|string|max:255',
            'per_city' => 'sometimes|string|max:255',
            'per_provience' => 'sometimes|string|max:255',
            'per_zip' => 'sometimes|string|max:10',
        ];
    }

    /**
     * @param Validator $validator
     * @return HttpResponseException
     */
    public function failedValidation(Validator $validator): HttpResponseException
    {
        throw new HttpResponseException(
            $this->error(
                $validator->errors()->messages(),
                ValidationConstants::ERROR,
                Response::HTTP_NOT_FOUND
            )
        );
    }
}
