<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MonthlySupportProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'monthly_support_programs';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'donation_taker_id',
        'monthly_amount',
        'support_type',
        'start_date',
        'end_date',
        'assigned_volunteer_id',
        'created_by',
        'status',
        'total_payments_made',
        'total_amount_paid',
        'next_payment_due',
    ];

    protected $casts = [
        'donation_taker_id' => 'integer',
        'monthly_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'assigned_volunteer_id' => 'integer',
        'created_by' => 'integer',
        'total_payments_made' => 'integer',
        'total_amount_paid' => 'decimal:2',
        'next_payment_due' => 'date',
    ];

    // Relationships
    public function donationTaker()
    {
        return $this->belongsTo(User::class, 'donation_taker_id');
    }

    public function assignedVolunteer()
    {
        return $this->belongsTo(User::class, 'assigned_volunteer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function monthlyPayments()
    {
        return $this->hasMany(MonthlyPayment::class, 'support_program_id');
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsOverdueAttribute()
    {
        return $this->next_payment_due && $this->next_payment_due < now() && $this->status === 'active';
    }

    public function getAverageMonthlyPaymentAttribute()
    {
        if ($this->total_payments_made == 0) {
            return 0;
        }
        return round($this->total_amount_paid / $this->total_payments_made, 2);
    }

    public function getMonthsRemainingAttribute()
    {
        if (!$this->end_date || $this->status !== 'active') {
            return null;
        }
        return now()->diffInMonths($this->end_date);
    }

    public function getTotalExpectedAmountAttribute()
    {
        if (!$this->end_date) {
            return null;
        }
        $monthsTotal = $this->start_date->diffInMonths($this->end_date);
        return $this->monthly_amount * $monthsTotal;
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByDonationTaker($query, $donationTakerId)
    {
        return $query->where('donation_taker_id', $donationTakerId);
    }

    public function scopeByVolunteer($query, $volunteerId)
    {
        return $query->where('assigned_volunteer_id', $volunteerId);
    }

    public function scopeBySupportType($query, $type)
    {
        return $query->where('support_type', $type);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->where('next_payment_due', '<', now());
    }

    public function scopeDueThisMonth($query)
    {
        return $query->where('status', 'active')
            ->whereMonth('next_payment_due', now()->month)
            ->whereYear('next_payment_due', now()->year);
    }

    public function scopeDueInDays($query, $days)
    {
        return $query->where('status', 'active')
            ->where('next_payment_due', '<=', now()->addDays($days))
            ->where('next_payment_due', '>=', now());
    }

    public function scopeOrderByDueDate($query, $direction = 'asc')
    {
        return $query->orderBy('next_payment_due', $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['donation_taker_id'] ?? null, function ($query, $donationTakerId) {
            $query->where('donation_taker_id', $donationTakerId);
        })->when($filters['assigned_volunteer_id'] ?? null, function ($query, $volunteerId) {
            $query->where('assigned_volunteer_id', $volunteerId);
        })->when($filters['support_type'] ?? null, function ($query, $type) {
            $query->where('support_type', $type);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['amount_range'] ?? null, function ($query, $range) {
            if (isset($range['min'])) {
                $query->where('monthly_amount', '>=', $range['min']);
            }
            if (isset($range['max'])) {
                $query->where('monthly_amount', '<=', $range['max']);
            }
        });
    }

    // Methods
    public function calculateNextPaymentDue()
    {
        $lastPayment = $this->monthlyPayments()
            ->where('status', 'completed')
            ->orderBy('payment_month', 'desc')
            ->first();

        if ($lastPayment) {
            $nextMonth = \Carbon\Carbon::createFromFormat('Y-m', $lastPayment->payment_month)->addMonth();
        } else {
            $nextMonth = $this->start_date->copy();
        }

        // Ensure we don't go past end date
        if ($this->end_date && $nextMonth > $this->end_date) {
            return null;
        }

        return $nextMonth->firstOfMonth();
    }

    public function processPayment($amount, $deliveredBy, $deliveryMethod, $notes = null)
    {
        $paymentMonth = $this->next_payment_due->format('Y-m');

        $payment = $this->monthlyPayments()->create([
            'amount' => $amount,
            'payment_month' => $paymentMonth,
            'delivered_by' => $deliveredBy,
            'delivery_method' => $deliveryMethod,
            'status' => 'completed',
            'delivered_at' => now(),
            'notes' => $notes,
        ]);

        // Update program stats
        $this->increment('total_payments_made');
        $this->increment('total_amount_paid', $amount);
        $this->next_payment_due = $this->calculateNextPaymentDue();

        // Check if program should be completed
        if (!$this->next_payment_due || ($this->end_date && $this->next_payment_due > $this->end_date)) {
            $this->status = 'completed';
        }

        $this->save();

        return $payment;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::id();
            }

            // Set initial next payment due if not provided
            if (!$model->next_payment_due) {
                $model->next_payment_due = $model->start_date->firstOfMonth();
            }
        });

        static::updating(function ($model) {
            // Auto-complete if end date is reached
            if ($model->isDirty('next_payment_due') && $model->end_date && $model->next_payment_due > $model->end_date) {
                $model->status = 'completed';
            }
        });
    }
}
