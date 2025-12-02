<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MonthlyPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'monthly_payments';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'support_program_id',
        'amount',
        'payment_month',
        'delivered_by',
        'delivery_method',
        'status',
        'delivered_at',
        'verified_at',
        'notes',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'support_program_id' => 'integer',
        'amount' => 'decimal:2',
        'delivered_by' => 'integer',
        'delivered_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function supportProgram()
    {
        return $this->belongsTo(MonthlySupportProgram::class, 'support_program_id');
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
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
    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsVerifiedAttribute()
    {
        return !is_null($this->verified_at);
    }

    public function getPaymentMonthNameAttribute()
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->payment_month)->format('F Y');
    }

    public function getDaysLateAttribute()
    {
        if ($this->status === 'completed') {
            return 0;
        }

        $dueDate = \Carbon\Carbon::createFromFormat('Y-m', $this->payment_month)->firstOfMonth();
        return max(0, now()->diffInDays($dueDate));
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('support_program_id', $programId);
    }

    public function scopeByVolunteer($query, $volunteerId)
    {
        return $query->where('delivered_by', $volunteerId);
    }

    public function scopeByDeliveryMethod($query, $method)
    {
        return $query->where('delivery_method', $method);
    }

    public function scopeByMonth($query, $year, $month)
    {
        $paymentMonth = sprintf('%04d-%02d', $year, $month);
        return $query->where('payment_month', $paymentMonth);
    }

    public function scopeThisMonth($query)
    {
        $currentMonth = now()->format('Y-m');
        return $query->where('payment_month', $currentMonth);
    }

    public function scopeOverdue($query)
    {
        $currentMonth = now()->format('Y-m');
        return $query->where('payment_month', '<', $currentMonth)
            ->whereIn('status', ['scheduled', 'failed']);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    public function scopeOrderByMonth($query, $direction = 'desc')
    {
        return $query->orderBy('payment_month', $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['support_program_id'] ?? null, function ($query, $programId) {
            $query->where('support_program_id', $programId);
        })->when($filters['delivered_by'] ?? null, function ($query, $deliveredBy) {
            $query->where('delivered_by', $deliveredBy);
        })->when($filters['delivery_method'] ?? null, function ($query, $method) {
            $query->where('delivery_method', $method);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['payment_month'] ?? null, function ($query, $month) {
            $query->where('payment_month', $month);
        })->when($filters['verified'] ?? null, function ($query, $verified) {
            if ($verified) {
                $query->whereNotNull('verified_at');
            } else {
                $query->whereNull('verified_at');
            }
        });
    }

    // Methods
    public function verify($notes = null)
    {
        $this->verified_at = now();
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Verified: " . $notes;
        }
        $this->save();

        return $this;
    }

    public function markAsCompleted($deliveredBy = null, $notes = null)
    {
        $this->status = 'completed';
        $this->delivered_at = now();

        if ($deliveredBy) {
            $this->delivered_by = $deliveredBy;
        }

        if ($notes) {
            $this->notes = $notes;
        }

        $this->save();

        return $this;
    }

    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';

        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Failed: " . $reason;
        }

        $this->save();

        return $this;
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
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }
        });

        static::updated(function ($model) {
            // Update support program stats when payment status changes
            if ($model->isDirty('status') && $model->status === 'completed') {
                $program = $model->supportProgram;
                $program->next_payment_due = $program->calculateNextPaymentDue();
                $program->save();
            }
        });
    }
}
