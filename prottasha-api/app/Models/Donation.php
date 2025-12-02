<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Donation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'donations';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'donor_id',
        'amount',
        'currency',
        'description',
        'donation_type',
        'project_id',
        'payment_method',
        'transaction_id',
        'payment_status',
        'payment_gateway',
        'allocated_amount',
        'donated_at',
        'payment_completed_at',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'donor_id' => 'integer',
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'project_id' => 'integer',
        'donated_at' => 'datetime',
        'payment_completed_at' => 'datetime',
    ];

    protected $appends = ['remaining_amount'];

    // Accessors
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->allocated_amount;
    }

    public function getIsFullyAllocatedAttribute()
    {
        return $this->remaining_amount <= 0;
    }

    // Relationships
    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function allocations()
    {
        return $this->hasMany(DonationAllocation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    public function scopeByDonor($query, $donorId)
    {
        return $query->where('donor_id', $donorId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeOneTime($query)
    {
        return $query->where('donation_type', 'one_time');
    }

    public function scopeRecurring($query)
    {
        return $query->where('donation_type', 'recurring_instance');
    }

    public function scopeUnallocated($query)
    {
        return $query->whereRaw('amount > allocated_amount');
    }

    public function scopeFullyAllocated($query)
    {
        return $query->whereRaw('amount <= allocated_amount');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('donated_at', [$startDate, $endDate]);
    }

    public function scopeByAmount($query, $minAmount = null, $maxAmount = null)
    {
        return $query->when($minAmount, fn($q) => $q->where('amount', '>=', $minAmount))
            ->when($maxAmount, fn($q) => $q->where('amount', '<=', $maxAmount));
    }

    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('donated_at', $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['donor_id'] ?? null, function ($query, $donorId) {
            $query->where('donor_id', $donorId);
        })->when($filters['project_id'] ?? null, function ($query, $projectId) {
            $query->where('project_id', $projectId);
        })->when($filters['payment_status'] ?? null, function ($query, $status) {
            $query->where('payment_status', $status);
        })->when($filters['donation_type'] ?? null, function ($query, $type) {
            $query->where('donation_type', $type);
        })->when($filters['payment_method'] ?? null, function ($query, $method) {
            $query->where('payment_method', $method);
        })->when($filters['amount_range'] ?? null, function ($query, $range) {
            if (isset($range['min'])) {
                $query->where('amount', '>=', $range['min']);
            }
            if (isset($range['max'])) {
                $query->where('amount', '<=', $range['max']);
            }
        })->when($filters['date_range'] ?? null, function ($query, $range) {
            if (isset($range['start'])) {
                $query->where('donated_at', '>=', $range['start']);
            }
            if (isset($range['end'])) {
                $query->where('donated_at', '<=', $range['end']);
            }
        });
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

            if (!$model->donated_at) {
                $model->donated_at = now();
            }
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }

            // Update payment completed timestamp when status changes to completed
            if ($model->isDirty('payment_status') && $model->payment_status === 'completed' && !$model->payment_completed_at) {
                $model->payment_completed_at = now();
            }
        });
    }
}
