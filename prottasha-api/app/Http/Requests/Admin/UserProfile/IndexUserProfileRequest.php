<?php

namespace App\Http\Requests\Admin\UserProfile;

use Illuminate\Foundation\Http\FormRequest;

class IndexUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paginate' => 'sometimes|boolean',
            'length' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
            'user_id' => 'sometimes|integer|exists:users,id',
            'country_id' => 'sometimes|integer|exists:countries,id',
            'division_id' => 'sometimes|integer|exists:divisions,id',
            'district_id' => 'sometimes|integer|exists:districts,id',
            'gender' => 'sometimes|string|in:male,female,other',
            'is_verified' => 'sometimes|boolean',
            'sort_by' => 'sometimes|string|in:first_name,last_name,created_at,updated_at',
            'sort_order' => 'sometimes|string|in:asc,desc',
        ];
    }
}
