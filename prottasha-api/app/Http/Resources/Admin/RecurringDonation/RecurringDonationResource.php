<?php

namespace App\Http\Resources\Admin\RecurringDonation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringDonationResource extends JsonResource
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
            'donor_id' => $this->donor_id,
            'project_id' => $this->project_id,
            'amount' => $this->amount,
            'frequency' => $this->frequency,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'next_payment_date' => $this->next_payment_date,
            'payment_method' => $this->payment_method,
            'payment_gateway' => $this->payment_gateway,
            'gateway_customer_id' => $this->gateway_customer_id,
            'gateway_subscription_id' => $this->gateway_subscription_id,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'donor' => $this->whenLoaded('donor', fn() => [
                'id' => $this->donor?->id,
                'name' => $this->donor?->name,
                'email' => $this->donor?->email,
                'user_profile' => $this->whenLoaded('donor.userProfile', fn() => [
                    'phone' => $this->donor?->userProfile?->phone,
                    'address' => $this->donor?->userProfile?->address,
                    'city' => $this->donor?->userProfile?->city,
                ]),
            ]),
            'project' => $this->whenLoaded('project', fn() => [
                'id' => $this->project?->id,
                'title' => $this->project?->title,
                'slug' => $this->project?->slug,
                'category' => $this->project?->category,
                'status' => $this->project?->status,
            ]),
        ];
    }
}
