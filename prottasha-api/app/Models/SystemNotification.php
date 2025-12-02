<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $table = 'system_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'priority',
        'related_entity_type',
        'related_entity_id',
        'read_at',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'related_entity_id' => 'integer',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    public function getIsUnreadAttribute()
    {
        return is_null($this->read_at);
    }

    public function getIsHighPriorityAttribute()
    {
        return in_array($this->priority, ['high', 'urgent']);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("CASE priority
            WHEN 'urgent' THEN 1
            WHEN 'high' THEN 2
            WHEN 'medium' THEN 3
            WHEN 'low' THEN 4
            END")
            ->orderBy('created_at', 'desc');
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['user_id'] ?? null, function ($query, $userId) {
            $query->where('user_id', $userId);
        })->when($filters['type'] ?? null, function ($query, $type) {
            $query->where('type', $type);
        })->when($filters['priority'] ?? null, function ($query, $priority) {
            $query->where('priority', $priority);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['read'] ?? null, function ($query, $read) {
            if ($read) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        })->when($filters['related_entity_type'] ?? null, function ($query, $entityType) {
            $query->where('related_entity_type', $entityType);
        });
    }

    // Methods
    public function markAsRead()
    {
        if ($this->is_unread) {
            $this->read_at = now();
            $this->status = 'read';
            $this->save();
        }

        return $this;
    }

    public function markAsUnread()
    {
        if ($this->is_read) {
            $this->update([
                'read_at' => null,
                'status' => 'sent'
            ]);
        }

        return $this;
    }

    public function archive()
    {
        $this->status = 'archived';
        $this->save();

        return $this;
    }

    // Static methods for creating notifications
    public static function createForUser($userId, $title, $message, $type = 'info', $priority = 'medium', $relatedEntityType = null, $relatedEntityId = null)
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'related_entity_type' => $relatedEntityType,
            'related_entity_id' => $relatedEntityId,
            'status' => 'pending',
        ]);
    }

    public static function notifyDonationReceived($donor, $donation)
    {
        return self::createForUser(
            $donor->id,
            'Donation Received',
            "Thank you for your donation of {$donation->currency} {$donation->amount}. Your contribution makes a difference!",
            'success',
            'medium',
            'donation',
            $donation->id
        );
    }

    public static function notifyDeliveryCompleted($donationTaker, $delivery)
    {
        return self::createForUser(
            $donationTaker->id,
            'Donation Delivered',
            "A donation of {$delivery->amount_delivered} has been delivered to you via {$delivery->delivery_method}.",
            'success',
            'medium',
            'delivery',
            $delivery->id
        );
    }

    public static function notifyVolunteerAssigned($volunteer, $project)
    {
        return self::createForUser(
            $volunteer->id,
            'Project Assignment',
            "You have been assigned to project: {$project->title}",
            'info',
            'medium',
            'project',
            $project->id
        );
    }

    public static function notifyMonthlyPaymentDue($donationTaker, $program)
    {
        return self::createForUser(
            $donationTaker->id,
            'Monthly Support Payment Due',
            "Your monthly support payment of {$program->monthly_amount} is due.",
            'info',
            'high',
            'monthly_support',
            $program->id
        );
    }

    public static function notifyProjectCompletion($donor, $project)
    {
        return self::createForUser(
            $donor->id,
            'Project Completed',
            "The project '{$project->title}' that you supported has been completed. Thank you for your contribution!",
            'success',
            'medium',
            'project',
            $project->id
        );
    }
}
