<?php

namespace App\Http\Resources\Admin\Donation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\User\UserResource;
use App\Http\Resources\Admin\Project\ProjectResource;

class DonationResource extends JsonResource
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
            'donation_taker_id' => $this->donation_taker_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'donation_type' => $this->donation_type,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_reference' => $this->payment_reference,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'donor' => new UserResource($this->whenLoaded('donor')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'donation_taker' => new UserResource($this->whenLoaded('donationTaker')),
            'allocations' => DonationAllocationResource::collection($this->whenLoaded('allocations')),
            'deliveries' => DonationDeliveryResource::collection($this->whenLoaded('deliveries')),
        ];
    }
}
