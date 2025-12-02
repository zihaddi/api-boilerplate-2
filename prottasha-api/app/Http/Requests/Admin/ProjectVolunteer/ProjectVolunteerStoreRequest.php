<?php

namespace App\Http\Requests\Admin\ProjectVolunteer;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProjectVolunteerStoreRequest extends FormRequest
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
            'project_id' => 'required|integer|exists:projects,id',
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string|max:100',
            'assigned_date' => 'required|date',
            'status' => 'required|string|in:active,inactive,completed,suspended',
            'hours_committed' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'Selected project does not exist.',
            'user_id.required' => 'Volunteer is required.',
            'user_id.exists' => 'Selected volunteer does not exist.',
            'role.required' => 'Volunteer role is required.',
            'assigned_date.required' => 'Assignment date is required.',
            'status.required' => 'Status is required.',
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
