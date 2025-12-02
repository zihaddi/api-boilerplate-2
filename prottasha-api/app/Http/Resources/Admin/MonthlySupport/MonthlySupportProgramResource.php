<?php

namespace App\Http\Resources\Admin\MonthlySupport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\User\UserResource;

class MonthlySupportProgramResource extends JsonResource
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
            'beneficiary_id' => $this->beneficiary_id,
            'monthly_amount' => $this->monthly_amount,
            'currency' => $this->currency,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'payment_day' => $this->payment_day,
            'status' => $this->status,
            'total_paid' => $this->total_paid,
            'next_payment_date' => $this->next_payment_date,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'donor' => new UserResource($this->whenLoaded('donor')),
            'beneficiary' => new UserResource($this->whenLoaded('beneficiary')),
            'monthly_payments' => MonthlyPaymentResource::collection($this->whenLoaded('monthlyPayments')),
        ];
    }
}
