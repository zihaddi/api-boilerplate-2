<?php

namespace App\Http\Requests\Admin\SystemNotification;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class SystemNotificationStoreRequest extends FormRequest
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
            'user_id' => 'nullable|integer|exists:users,id',
            'type' => 'required|string|in:donation,project,payment,general,alert',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'data' => 'nullable|json',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'channels' => 'nullable|json',
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
            'type.required' => 'Notification type is required.',
            'type.in' => 'Invalid notification type.',
            'title.required' => 'Title is required.',
            'message.required' => 'Message is required.',
            'priority.required' => 'Priority is required.',
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
