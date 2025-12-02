<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class RecurringDonation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recurring_donations';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'donor_id',
        'amount',
        'currency',
        'description',
        'frequency',
        'project_id',
        'start_date',
        'end_date',
        'next_payment_date',
        'status',
        'total_payments_made',
        'total_amount_donated',
        'payment_method',
        'payment_token',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'donor_id' => 'integer',
        'amount' => 'decimal:2',
        'total_amount_donated' => 'decimal:2',
        'project_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_payment_date' => 'date',
        'total_payments_made' => 'integer',
    ];

    // Relationships
    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'project_id', 'project_id')
            ->where('donation_type', 'recurring_instance')
            ->where('donor_id', $this->donor_id);
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

    public function getNextPaymentAmountAttribute()
    {
        return $this->amount;
    }

    public function getAverageMonthlyDonationAttribute()
    {
        if ($this->total_payments_made == 0) {
            return 0;
        }
        return round($this->total_amount_donated / $this->total_payments_made, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByDonor($query, $donorId)
    {
        return $query->where('donor_id', $donorId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    public function scopeDueForPayment($query)
    {
        return $query->where('status', 'active')
            ->where('next_payment_date', '<=', now());
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', 'active')
            ->where('next_payment_date', '>', now())
            ->where('next_payment_date', '<=', now()->addDays($days));
    }

    public function scopeOrderByNextPayment($query, $direction = 'asc')
    {
        return $query->orderBy('next_payment_date', $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['donor_id'] ?? null, function ($query, $donorId) {
            $query->where('donor_id', $donorId);
        })->when($filters['project_id'] ?? null, function ($query, $projectId) {
            $query->where('project_id', $projectId);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['frequency'] ?? null, function ($query, $frequency) {
            $query->where('frequency', $frequency);
        })->when($filters['payment_method'] ?? null, function ($query, $method) {
            $query->where('payment_method', $method);
        });
    }

    // Methods
    public function calculateNextPaymentDate()
    {
        $currentDate = $this->next_payment_date ?: $this->start_date;

        switch ($this->frequency) {
            case 'weekly':
                return $currentDate->addWeek();
            case 'monthly':
                return $currentDate->addMonth();
            case 'quarterly':
                return $currentDate->addMonths(3);
            case 'yearly':
                return $currentDate->addYear();
            default:
                return $currentDate->addMonth();
        }
    }

    public function processPayment()
    {
        $this->increment('total_payments_made');
        $this->increment('total_amount_donated', $this->amount);
        $this->next_payment_date = $this->calculateNextPaymentDate();

        // Check if end date is reached
        if ($this->end_date && $this->next_payment_date > $this->end_date) {
            $this->status = 'completed';
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

            // Set initial next payment date if not provided
            if (!$model->next_payment_date) {
                $model->next_payment_date = $model->start_date;
            }
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }
        });
    }
}
