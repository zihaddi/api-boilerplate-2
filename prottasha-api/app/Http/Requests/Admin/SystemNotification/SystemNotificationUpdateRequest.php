<?php

namespace App\Http\Requests\Admin\SystemNotification;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class SystemNotificationUpdateRequest extends FormRequest
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
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
            'type' => 'sometimes|string|in:donation,project,payment,general,alert',
            'title' => 'sometimes|string|max:255',
            'message' => 'sometimes|string|max:1000',
            'data' => 'sometimes|nullable|json',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
            'is_read' => 'sometimes|boolean',
            'read_at' => 'sometimes|nullable|date',
            'channels' => 'sometimes|nullable|json',
            'sent_at' => 'sometimes|nullable|date',
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
            'user_id.exists' => 'Selected user does not exist.',
            'type.in' => 'Invalid notification type.',
            'priority.in' => 'Invalid priority level.',
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
