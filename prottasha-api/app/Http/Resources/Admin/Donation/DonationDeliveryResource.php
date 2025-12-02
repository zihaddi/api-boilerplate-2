<?php

namespace App\Http\Resources\Admin\Donation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationDeliveryResource extends JsonResource
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
            'donation_id' => $this->donation_id,
            'delivered_to_id' => $this->delivered_to_id,
            'amount_delivered' => $this->amount_delivered,
            'delivery_date' => $this->delivery_date,
            'delivery_method' => $this->delivery_method,
            'tracking_number' => $this->tracking_number,
            'delivery_address' => $this->delivery_address,
            'notes' => $this->notes,
            'status' => $this->status,
            'verification_code' => $this->verification_code,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at,
            'verified_by' => $this->verified_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
