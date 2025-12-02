<?php

namespace App\Http\Resources\Admin\SystemNotification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemNotificationResource extends JsonResource
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
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'priority' => $this->priority,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at,
            'channels' => $this->channels,
            'sent_at' => $this->sent_at,
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
        ];
    }
}
