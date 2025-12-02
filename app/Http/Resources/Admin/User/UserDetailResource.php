<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "user_id" => $this->user_id,
            "first_name" => $this->first_name,
            "middle_name" => $this->middle_name,
            "last_name" => $this->last_name,
            "photo" => $this->photo,
            "dob" => $this->dob,
            "religion_id" => $this->religion_id,
            "gender" => $this->gender,
            "occupation" => $this->occupation,
            "nationality_id" => $this->nationality_id,
            "vulnerability_info" => $this->vulnerability_info,
            "pre_country" => $this->pre_country,
            "pre_srteet_address" => $this->pre_srteet_address,
            "pre_city" => $this->pre_city,
            "pre_provience" => $this->pre_provience,
            "pre_zip" => $this->pre_zip,
            "same_as_present_address" => $this->same_as_present_address,
            "per_country" => $this->per_country,
            "per_srteet_address" => $this->per_srteet_address,
            "per_city" => $this->per_city,
            "per_provience" => $this->per_provience,
            "per_zip" => $this->per_zip,
            "nationalityInfo" => $this->nationalityInfo,


        ];
    }
}
