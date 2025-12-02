<?php

namespace App\Http\Resources\Admin\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'target_amount' => $this->target_amount,
            'raised_amount' => $this->raised_amount,
            'currency' => $this->currency,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'location' => $this->location,
            'district_id' => $this->district_id,
            'thana_id' => $this->thana_id,
            'status' => $this->status,
            'priority' => $this->priority,
            'completion_percentage' => $this->completion_percentage,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
