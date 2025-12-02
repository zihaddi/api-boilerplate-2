<?php

namespace App\Http\Requests\Admin\Project;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProjectUpdateRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'category_id' => 'sometimes|integer|exists:project_categories,id',
            'target_amount' => 'sometimes|numeric|min:1',
            'currency' => 'sometimes|string|max:3|in:BDT,USD,EUR',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'location' => 'sometimes|nullable|string|max:500',
            'district_id' => 'sometimes|integer|exists:districts,id',
            'thana_id' => 'sometimes|nullable|integer|exists:thanas,id',
            'status' => 'sometimes|string|in:planning,active,completed,cancelled,on_hold',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
            'completion_percentage' => 'sometimes|numeric|min:0|max:100',
            'image' => 'sometimes|nullable|string',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.max' => 'Project title cannot exceed 255 characters.',
            'category_id.exists' => 'Selected category does not exist.',
            'target_amount.min' => 'Target amount must be at least 1.',
            'currency.in' => 'Currency must be one of: BDT, USD, EUR.',
            'end_date.after' => 'End date must be after start date.',
            'district_id.exists' => 'Selected district does not exist.',
            'completion_percentage.min' => 'Completion percentage cannot be less than 0.',
            'completion_percentage.max' => 'Completion percentage cannot exceed 100.',
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
