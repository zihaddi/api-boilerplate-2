<?php

namespace App\Http\Resources\Admin\ProjectVolunteer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Project\ProjectResource;
use App\Http\Resources\Admin\User\UserResource;

class ProjectVolunteerResource extends JsonResource
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
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
            'role' => $this->role,
            'assigned_date' => $this->assigned_date,
            'status' => $this->status,
            'hours_committed' => $this->hours_committed,
            'hours_completed' => $this->hours_completed,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
