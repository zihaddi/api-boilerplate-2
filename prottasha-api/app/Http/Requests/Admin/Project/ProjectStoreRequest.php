<?php

namespace App\Http\Requests\Admin\Project;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ProjectStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category_id' => 'required|integer|exists:project_categories,id',
            'target_amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3|in:BDT,USD,EUR',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:500',
            'district_id' => 'required|integer|exists:districts,id',
            'thana_id' => 'nullable|integer|exists:thanas,id',
            'status' => 'required|string|in:planning,active,completed,cancelled,on_hold',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'image' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Project title is required.',
            'title.max' => 'Project title cannot exceed 255 characters.',
            'description.required' => 'Project description is required.',
            'category_id.required' => 'Project category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'target_amount.required' => 'Target amount is required.',
            'target_amount.min' => 'Target amount must be at least 1.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Currency must be one of: BDT, USD, EUR.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after start date.',
            'district_id.required' => 'District is required.',
            'district_id.exists' => 'Selected district does not exist.',
            'status.required' => 'Project status is required.',
            'priority.required' => 'Project priority is required.',
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
