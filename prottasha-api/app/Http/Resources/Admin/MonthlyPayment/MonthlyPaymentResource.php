<?php

namespace App\Http\Resources\Admin\MonthlyPayment;

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
            'supporter_id' => $this->supporter_id,
            'payment_date' => $this->payment_date,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_gateway' => $this->payment_gateway,
            'transaction_id' => $this->transaction_id,
            'payment_status' => $this->payment_status,
            'failure_reason' => $this->failure_reason,
            'gateway_response' => $this->gateway_response,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'supporter' => $this->whenLoaded('supporter', fn() => [
                'id' => $this->supporter?->id,
                'name' => $this->supporter?->name,
                'email' => $this->supporter?->email,
                'user_profile' => $this->whenLoaded('supporter.userProfile', fn() => [
                    'phone' => $this->supporter?->userProfile?->phone,
                    'address' => $this->supporter?->userProfile?->address,
                    'city' => $this->supporter?->userProfile?->city,
                ]),
            ]),
            'program' => $this->whenLoaded('program', fn() => [
                'id' => $this->program?->id,
                'title' => $this->program?->title,
                'monthly_amount' => $this->program?->monthly_amount,
                'category' => $this->program?->category,
                'status' => $this->program?->status,
            ]),
        ];
    }
}
