<?php

namespace App\Http\Requests\Admin\TreeEntity;

use App\Constants\ValidationConstants;
use App\Http\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class TreeEntityUpdateRequest extends FormRequest
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
        $id = $this->route('tree_entity'); // Get the ID from the route.
        return [
            'pid' => 'nullable|integer',
            'node_name' => 'required|string|max:255|unique:tree_entities,node_name,' . $id,
            'route_name' => 'nullable|string|max:255',
            'route_location' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'serials' => 'required|integer'
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
