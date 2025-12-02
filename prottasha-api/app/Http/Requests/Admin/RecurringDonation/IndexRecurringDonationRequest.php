<?php

namespace App\Http\Requests\Admin\RecurringDonation;

use Illuminate\Foundation\Http\FormRequest;

class IndexRecurringDonationRequest extends FormRequest
{
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
            'paginate' => 'sometimes|boolean',
            'length' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:active,paused,cancelled,completed',
            'frequency' => 'sometimes|string|in:monthly,quarterly,annually',
            'payment_method' => 'sometimes|string|max:50',
            'donor_id' => 'sometimes|integer|exists:users,id',
            'project_id' => 'sometimes|integer|exists:projects,id',
            'amount_min' => 'sometimes|numeric|min:0',
            'amount_max' => 'sometimes|numeric|min:0',
            'start_date_from' => 'sometimes|date',
            'start_date_to' => 'sometimes|date|after_or_equal:start_date_from',
            'sort_by' => 'sometimes|string|in:amount,start_date,created_at,updated_at',
            'sort_order' => 'sometimes|string|in:asc,desc',
        ];
    }
}
