<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class DonationAllocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'donation_allocations';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'donation_id',
        'project_id',
        'donation_taker_id',
        'allocated_amount',
        'allocation_type',
        'purpose',
        'allocated_by',
        'distributed_amount',
        'status',
        'allocated_at',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'donation_id' => 'integer',
        'project_id' => 'integer',
        'donation_taker_id' => 'integer',
        'allocated_amount' => 'decimal:2',
        'distributed_amount' => 'decimal:2',
        'allocated_by' => 'integer',
        'allocated_at' => 'datetime',
    ];

    protected $appends = ['remaining_amount'];

    // Accessors
    public function getRemainingAmountAttribute()
    {
        return $this->allocated_amount - $this->distributed_amount;
    }

    public function getIsFullyDistributedAttribute()
    {
        return $this->remaining_amount <= 0;
    }

    public function getDistributionPercentageAttribute()
    {
        if ($this->allocated_amount == 0) {
            return 0;
        }
        return round(($this->distributed_amount / $this->allocated_amount) * 100, 2);
    }

    // Relationships
    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function donationTaker()
    {
        return $this->belongsTo(User::class, 'donation_taker_id');
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function deliveries()
    {
        return $this->hasMany(DonationDelivery::class, 'allocation_id');
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
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByDonation($query, $donationId)
    {
        return $query->where('donation_id', $donationId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByDonationTaker($query, $donationTakerId)
    {
        return $query->where('donation_taker_id', $donationTakerId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('allocation_type', $type);
    }

    public function scopeProjectAllocations($query)
    {
        return $query->where('allocation_type', 'project')
            ->whereNotNull('project_id');
    }

    public function scopeDirectAllocations($query)
    {
        return $query->where('allocation_type', 'direct')
            ->whereNotNull('donation_taker_id');
    }

    public function scopeMonthlySupport($query)
    {
        return $query->where('allocation_type', 'monthly_support');
    }

    public function scopeUndistributed($query)
    {
        return $query->whereRaw('allocated_amount > distributed_amount');
    }

    public function scopeFullyDistributed($query)
    {
        return $query->whereRaw('allocated_amount <= distributed_amount');
    }

    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('allocated_at', $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['donation_id'] ?? null, function ($query, $donationId) {
            $query->where('donation_id', $donationId);
        })->when($filters['project_id'] ?? null, function ($query, $projectId) {
            $query->where('project_id', $projectId);
        })->when($filters['donation_taker_id'] ?? null, function ($query, $donationTakerId) {
            $query->where('donation_taker_id', $donationTakerId);
        })->when($filters['allocation_type'] ?? null, function ($query, $type) {
            $query->where('allocation_type', $type);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['allocated_by'] ?? null, function ($query, $allocatedBy) {
            $query->where('allocated_by', $allocatedBy);
        });
    }

    // Methods
    public function updateDistributedAmount()
    {
        $this->distributed_amount = $this->deliveries()->sum('amount_delivered');

        // Update status based on distribution
        if ($this->distributed_amount >= $this->allocated_amount) {
            $this->status = 'completed';
        } elseif ($this->distributed_amount > 0) {
            $this->status = 'active';
        }

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

            if (!$model->allocated_at) {
                $model->allocated_at = now();
            }
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }
        });
    }
}
