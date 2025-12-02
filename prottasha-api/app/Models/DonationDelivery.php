<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class DonationDelivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'donation_deliveries';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'allocation_id',
        'volunteer_id',
        'donation_taker_id',
        'amount_delivered',
        'delivery_method',
        'delivery_notes',
        'delivery_location',
        'recipient_confirmation',
        'recipient_signature_url',
        'verified_by',
        'verification_notes',
        'delivered_at',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'allocation_id' => 'integer',
        'volunteer_id' => 'integer',
        'donation_taker_id' => 'integer',
        'amount_delivered' => 'decimal:2',
        'recipient_confirmation' => 'boolean',
        'verified_by' => 'integer',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function allocation()
    {
        return $this->belongsTo(DonationAllocation::class, 'allocation_id');
    }

    public function volunteer()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function donationTaker()
    {
        return $this->belongsTo(User::class, 'donation_taker_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
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
    public function getIsVerifiedAttribute()
    {
        return !is_null($this->verified_by);
    }

    public function getIsConfirmedAttribute()
    {
        return $this->recipient_confirmation;
    }

    public function getDeliveryStatusAttribute()
    {
        if ($this->is_verified) {
            return 'verified';
        } elseif ($this->is_confirmed) {
            return 'confirmed';
        } else {
            return 'delivered';
        }
    }

    // Scopes
    public function scopeByAllocation($query, $allocationId)
    {
        return $query->where('allocation_id', $allocationId);
    }

    public function scopeByVolunteer($query, $volunteerId)
    {
        return $query->where('volunteer_id', $volunteerId);
    }

    public function scopeByDonationTaker($query, $donationTakerId)
    {
        return $query->where('donation_taker_id', $donationTakerId);
    }

    public function scopeByDeliveryMethod($query, $method)
    {
        return $query->where('delivery_method', $method);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('recipient_confirmation', true);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->where('recipient_confirmation', false);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_by');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_by');
    }

    public function scopeCash($query)
    {
        return $query->where('delivery_method', 'cash');
    }

    public function scopeBankTransfer($query)
    {
        return $query->where('delivery_method', 'bank_transfer');
    }

    public function scopeMobileMoney($query)
    {
        return $query->where('delivery_method', 'mobile_money');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('delivered_at', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('delivered_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('delivered_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('delivered_at', now()->month)
            ->whereYear('delivered_at', now()->year);
    }

    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('delivered_at', $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['allocation_id'] ?? null, function ($query, $allocationId) {
            $query->where('allocation_id', $allocationId);
        })->when($filters['volunteer_id'] ?? null, function ($query, $volunteerId) {
            $query->where('volunteer_id', $volunteerId);
        })->when($filters['donation_taker_id'] ?? null, function ($query, $donationTakerId) {
            $query->where('donation_taker_id', $donationTakerId);
        })->when($filters['delivery_method'] ?? null, function ($query, $method) {
            $query->where('delivery_method', $method);
        })->when($filters['verified'] ?? null, function ($query, $verified) {
            if ($verified) {
                $query->whereNotNull('verified_by');
            } else {
                $query->whereNull('verified_by');
            }
        })->when($filters['confirmed'] ?? null, function ($query, $confirmed) {
            $query->where('recipient_confirmation', $confirmed);
        })->when($filters['date_range'] ?? null, function ($query, $range) {
            if (isset($range['start'])) {
                $query->where('delivered_at', '>=', $range['start']);
            }
            if (isset($range['end'])) {
                $query->where('delivered_at', '<=', $range['end']);
            }
        });
    }

    // Methods
    public function verify($verifiedBy = null, $notes = null)
    {
        $this->verified_by = $verifiedBy ?: Auth::id();
        $this->verification_notes = $notes;
        $this->save();

        // Update allocation distributed amount
        $this->allocation->updateDistributedAmount();

        return $this;
    }

    public function confirm($signature = null)
    {
        $this->recipient_confirmation = true;
        if ($signature) {
            $this->recipient_signature_url = $signature;
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

            if (!$model->delivered_at) {
                $model->delivered_at = now();
            }
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }
        });

        static::created(function ($model) {
            // Update allocation distributed amount
            $model->allocation->updateDistributedAmount();

            // Update volunteer performance stats
            $volunteerProject = ProjectVolunteer::where('volunteer_id', $model->volunteer_id)
                ->whereHas('allocation.project', function ($query) use ($model) {
                    $query->where('id', $model->allocation->project_id);
                })->first();

            if ($volunteerProject) {
                $volunteerProject->updatePerformanceStats();
            }
        });
    }
}
