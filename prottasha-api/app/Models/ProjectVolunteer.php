<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ProjectVolunteer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_volunteers';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'project_id',
        'volunteer_id',
        'role',
        'responsibilities',
        'assigned_at',
        'assignment_start_date',
        'assignment_end_date',
        'status',
        'deliveries_count',
        'total_amount_delivered',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'volunteer_id' => 'integer',
        'assigned_at' => 'datetime',
        'assignment_start_date' => 'date',
        'assignment_end_date' => 'date',
        'deliveries_count' => 'integer',
        'total_amount_delivered' => 'decimal:2',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function volunteer()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function deliveries()
    {
        return $this->hasMany(DonationDelivery::class, 'volunteer_id', 'volunteer_id')
            ->whereHas('allocation', function ($query) {
                $query->where('project_id', $this->project_id);
            });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getAverageDeliveryAmountAttribute()
    {
        if ($this->deliveries_count == 0) {
            return 0;
        }
        return round($this->total_amount_delivered / $this->deliveries_count, 2);
    }

    public function getDaysAssignedAttribute()
    {
        $startDate = $this->assignment_start_date ?: $this->assigned_at;
        $endDate = $this->assignment_end_date ?: now();
        return $startDate->diffInDays($endDate);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByVolunteer($query, $volunteerId)
    {
        return $query->where('volunteer_id', $volunteerId);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeCoordinators($query)
    {
        return $query->where('role', 'coordinator');
    }

    public function scopeFieldWorkers($query)
    {
        return $query->where('role', 'field_worker');
    }

    public function scopeCurrentlyAssigned($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('assignment_end_date')
                  ->orWhere('assignment_end_date', '>=', now());
            });
    }

    public function scopeOrderByPerformance($query, $direction = 'desc')
    {
        return $query->orderBy('total_amount_delivered', $direction)
            ->orderBy('deliveries_count', $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['project_id'] ?? null, function ($query, $projectId) {
            $query->where('project_id', $projectId);
        })->when($filters['volunteer_id'] ?? null, function ($query, $volunteerId) {
            $query->where('volunteer_id', $volunteerId);
        })->when($filters['role'] ?? null, function ($query, $role) {
            $query->where('role', $role);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        });
    }

    // Methods
    public function updatePerformanceStats()
    {
        $deliveries = $this->deliveries();
        $this->deliveries_count = $deliveries->count();
        $this->total_amount_delivered = $deliveries->sum('amount_delivered');
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::id();
            }
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }

            if (!$model->assigned_at) {
                $model->assigned_at = now();
            }
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }
        });
    }
}
