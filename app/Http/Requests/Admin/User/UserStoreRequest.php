<?php

namespace App\Http\Requests\Admin\User;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UserStoreRequest extends FormRequest
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
            'mobile' => 'required|string|max:15|unique:users,mobile',
            'ccode' => 'nullable|string|max:5',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'auth_code' => 'nullable|string|max:10',
            'is_verify' => 'nullable|boolean',
            'status' => 'required|boolean',
            'user_type' => 'required|integer',
            'photo' => 'nullable|string',

            // UserInfo data validation
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'religion_id' => 'nullable|integer',
            'gender' => 'nullable|integer',
            'occupation' => 'nullable|string|max:255',
            'nationality_id' => 'nullable|integer',
            'vulnerability_info' => 'nullable|string|max:255',
            'pre_country' => 'sometimes|integer',
            'pre_srteet_address' => 'nullable|string|max:255',
            'pre_city' => 'nullable|string|max:255',
            'pre_provience' => 'nullable|string|max:255',
            'pre_zip' => 'nullable|string|max:10',
            'same_as_present_address' => 'nullable|boolean',
            'per_country' => 'required_if:same_as_present_address,false|integer',
            'per_srteet_address' => 'required_if:same_as_present_address,false|string|max:255',
            'per_city' => 'required_if:same_as_present_address,false|string|max:255',
            'per_provience' => 'required_if:same_as_present_address,false|string|max:255',
            'per_zip' => 'required_if:same_as_present_address,false|string|max:10',
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
