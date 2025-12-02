<?php

namespace App\Http\Resources\Admin\MonthlySupport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyPaymentResource extends JsonResource
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
            'program_id' => $this->program_id,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'due_date' => $this->due_date,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at,
            'verified_by' => $this->verified_by,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
