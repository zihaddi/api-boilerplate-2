<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'mobile' => $this->mobile,
            'ccode' => $this->ccode,
            'email' => $this->email,
            'is_verify' => $this->is_verify,
            'status' => $this->status,
            'photo' => $this->photo,
            'mobile_verified_at' => $this->mobile_verified_at,
            'email_verified_at' => $this->email_verified_at,
            'user_type' => $this->user_type,
            'created_at' => $this->created_at,
            'user_info' => new UserDetailResource($this->whenLoaded('UserInfo')),
        ];
    }
}
