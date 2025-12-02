<?php

namespace App\Http\Resources\Admin\UserProfile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'country_id' => $this->country_id,
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'upazila_id' => $this->upazila_id,
            'union_id' => $this->union_id,
            'postal_code' => $this->postal_code,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'occupation' => $this->occupation,
            'education' => $this->education,
            'marital_status' => $this->marital_status,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relationship' => $this->emergency_contact_relationship,
            'profile_picture' => $this->profile_picture,
            'national_id' => $this->national_id,
            'passport_number' => $this->passport_number,
            'is_verified' => $this->is_verified,
            'verification_documents' => $this->verification_documents,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'role' => $this->user?->role,
            ]),
            'country' => $this->whenLoaded('country', fn() => [
                'id' => $this->country?->id,
                'name' => $this->country?->name,
                'code' => $this->country?->code,
            ]),
            'division' => $this->whenLoaded('division', fn() => [
                'id' => $this->division?->id,
                'name' => $this->division?->name,
                'bn_name' => $this->division?->bn_name,
            ]),
            'district' => $this->whenLoaded('district', fn() => [
                'id' => $this->district?->id,
                'name' => $this->district?->name,
                'bn_name' => $this->district?->bn_name,
            ]),
            'thana' => $this->whenLoaded('thana', fn() => [
                'id' => $this->thana?->id,
                'name' => $this->thana?->name,
                'bn_name' => $this->thana?->bn_name,
            ]),
            'upazila' => $this->whenLoaded('upazila', fn() => [
                'id' => $this->upazila?->id,
                'name' => $this->upazila?->name,
                'bn_name' => $this->upazila?->bn_name,
            ]),
            'union' => $this->whenLoaded('union', fn() => [
                'id' => $this->union?->id,
                'name' => $this->union?->name,
                'bn_name' => $this->union?->bn_name,
            ]),
        ];
    }
}
