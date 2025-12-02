<?php

namespace App\Http\Resources\Admin\Donation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationAllocationResource extends JsonResource
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
            'allocated_to_id' => $this->allocated_to_id,
            'amount_allocated' => $this->amount_allocated,
            'allocation_date' => $this->allocation_date,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
