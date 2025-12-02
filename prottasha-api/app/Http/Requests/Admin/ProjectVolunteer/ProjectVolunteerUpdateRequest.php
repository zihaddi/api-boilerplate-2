<?php

namespace App\Http\Requests\Admin\ProjectVolunteer;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProjectVolunteerUpdateRequest extends FormRequest
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
            'project_id' => 'sometimes|integer|exists:projects,id',
            'user_id' => 'sometimes|integer|exists:users,id',
            'role' => 'sometimes|string|max:100',
            'assigned_date' => 'sometimes|date',
            'status' => 'sometimes|string|in:active,inactive,completed,suspended',
            'hours_committed' => 'sometimes|nullable|numeric|min:0',
            'hours_completed' => 'sometimes|nullable|numeric|min:0',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'project_id.exists' => 'Selected project does not exist.',
            'user_id.exists' => 'Selected volunteer does not exist.',
            'status.in' => 'Status must be one of: active, inactive, completed, suspended.',
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
