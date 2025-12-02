<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'entity_id' => 'integer',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByEntity($query, $entityType, $entityId = null)
    {
        return $query->where('entity_type', $entityType)
            ->when($entityId, fn($q) => $q->where('entity_id', $entityId));
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOrderByRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['user_id'] ?? null, function ($query, $userId) {
            $query->where('user_id', $userId);
        })->when($filters['action'] ?? null, function ($query, $action) {
            $query->where('action', $action);
        })->when($filters['entity_type'] ?? null, function ($query, $entityType) {
            $query->where('entity_type', $entityType);
        })->when($filters['entity_id'] ?? null, function ($query, $entityId) {
            $query->where('entity_id', $entityId);
        })->when($filters['date_range'] ?? null, function ($query, $range) {
            if (isset($range['start'])) {
                $query->where('created_at', '>=', $range['start']);
            }
            if (isset($range['end'])) {
                $query->where('created_at', '<=', $range['end']);
            }
        });
    }

    // Static methods for logging
    public static function logActivity($action, $entityType, $entityId, $oldValues = null, $newValues = null)
    {
        return self::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logDonationActivity($action, $donation, $oldValues = null, $newValues = null)
    {
        return self::logActivity($action, 'donation', $donation->id, $oldValues, $newValues);
    }

    public static function logProjectActivity($action, $project, $oldValues = null, $newValues = null)
    {
        return self::logActivity($action, 'project', $project->id, $oldValues, $newValues);
    }

    public static function logDeliveryActivity($action, $delivery, $oldValues = null, $newValues = null)
    {
        return self::logActivity($action, 'delivery', $delivery->id, $oldValues, $newValues);
    }

    public static function logAllocationActivity($action, $allocation, $oldValues = null, $newValues = null)
    {
        return self::logActivity($action, 'allocation', $allocation->id, $oldValues, $newValues);
    }

    // Specific logging methods
    public static function logDonationCreated($donation)
    {
        return self::logDonationActivity('created', $donation);
    }

    public static function logDonationUpdated($donation, $oldValues = null, $newValues = null)
    {
        return self::logDonationActivity('updated', $donation, $oldValues, $newValues);
    }

    public static function logDonationDeleted($donation)
    {
        return self::logDonationActivity('deleted', $donation);
    }

    public static function logDonationRestored($donation)
    {
        return self::logDonationActivity('restored', $donation);
    }

    public static function logProjectCreated($project)
    {
        return self::logProjectActivity('created', $project);
    }

    public static function logProjectUpdated($project, $oldValues = null, $newValues = null)
    {
        return self::logProjectActivity('updated', $project, $oldValues, $newValues);
    }

    public static function logProjectDeleted($project)
    {
        return self::logProjectActivity('deleted', $project);
    }

    public static function logProjectRestored($project)
    {
        return self::logProjectActivity('restored', $project);
    }

    public static function logDeliveryCreated($delivery)
    {
        return self::logDeliveryActivity('created', $delivery);
    }

    public static function logDeliveryUpdated($delivery, $oldValues = null, $newValues = null)
    {
        return self::logDeliveryActivity('updated', $delivery, $oldValues, $newValues);
    }

    public static function logDeliveryDeleted($delivery)
    {
        return self::logDeliveryActivity('deleted', $delivery);
    }

    public static function logDeliveryRestored($delivery)
    {
        return self::logDeliveryActivity('restored', $delivery);
    }

    public static function logAllocationCreated($allocation)
    {
        return self::logAllocationActivity('created', $allocation);
    }

    public static function logAllocationUpdated($allocation, $oldValues = null, $newValues = null)
    {
        return self::logAllocationActivity('updated', $allocation, $oldValues, $newValues);
    }

    public static function logAllocationDeleted($allocation)
    {
        return self::logAllocationActivity('deleted', $allocation);
    }

    public static function logAllocationRestored($allocation)
    {
        return self::logAllocationActivity('restored', $allocation);
    }

    public static function logMonthlySupportCreated($program)
    {
        return self::logActivity('created', 'monthly_support', $program->id);
    }

    public static function logMonthlySupportUpdated($program, $oldValues = null, $newValues = null)
    {
        return self::logActivity('updated', 'monthly_support', $program->id, $oldValues, $newValues);
    }

    public static function logMonthlySupportDeleted($program)
    {
        return self::logActivity('deleted', 'monthly_support', $program->id);
    }

    public static function logMonthlySupportRestored($program)
    {
        return self::logActivity('restored', 'monthly_support', $program->id);
    }
}
