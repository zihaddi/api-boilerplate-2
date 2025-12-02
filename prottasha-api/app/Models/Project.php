<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'target_amount',
        'raised_amount',
        'allocated_amount',
        'distributed_amount',
        'start_date',
        'end_date',
        'country_id',
        'division_id',
        'district_id',
        'thana_id',
        'upazila_id',
        'union_id',
        'created_by',
        'managed_by',
        'status',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'target_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'distributed_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'country_id' => 'integer',
        'division_id' => 'integer',
        'district_id' => 'integer',
        'thana_id' => 'integer',
        'upazila_id' => 'integer',
        'union_id' => 'integer',
        'created_by' => 'integer',
        'managed_by' => 'integer',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function managedBy()
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function recurringDonations()
    {
        return $this->hasMany(RecurringDonation::class);
    }

    public function volunteers()
    {
        return $this->hasMany(ProjectVolunteer::class);
    }

    public function allocations()
    {
        return $this->hasMany(DonationAllocation::class);
    }

    // Accessors
    public function getRemainingAmountAttribute()
    {
        return $this->target_amount - $this->raised_amount;
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->target_amount == 0) {
            return 0;
        }
        return round(($this->raised_amount / $this->target_amount) * 100, 2);
    }

    public function getUnallocatedAmountAttribute()
    {
        return $this->raised_amount - $this->allocated_amount;
    }

    public function getUndistributedAmountAttribute()
    {
        return $this->allocated_amount - $this->distributed_amount;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByLocation($query, $country = null, $division = null, $district = null)
    {
        return $query->when($country, fn($q) => $q->where('country_id', $country))
            ->when($division, fn($q) => $q->where('division_id', $division))
            ->when($district, fn($q) => $q->where('district_id', $district));
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereIn('status', ['active', 'planning']);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('start_date', $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['title'] ?? null, function ($query, $title) {
            $query->where('title', 'like', '%' . $title . '%');
        })->when($filters['category_id'] ?? null, function ($query, $categoryId) {
            $query->where('category_id', $categoryId);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['country_id'] ?? null, function ($query, $countryId) {
            $query->where('country_id', $countryId);
        })->when($filters['managed_by'] ?? null, function ($query, $managedBy) {
            $query->where('managed_by', $managedBy);
        })->when($filters['date_range'] ?? null, function ($query, $dateRange) {
            if (isset($dateRange['start'])) {
                $query->where('start_date', '>=', $dateRange['start']);
            }
            if (isset($dateRange['end'])) {
                $query->where('end_date', '<=', $dateRange['end']);
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
        });

        static::updating(function ($model) {
            // Auto-update status based on dates and amounts
            if ($model->isDirty('end_date') && $model->end_date < now() && $model->status === 'active') {
                $model->status = 'completed';
            }
        });
    }
}
