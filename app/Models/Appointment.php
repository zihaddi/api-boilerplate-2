<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'appointments';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'pet_id',
        'professional_id',
        'professional_type',
        'service_id',
        'scheduled_datetime',
        'duration_minutes',
        'location_type',
        'status',
        'base_cost',
        'additional_fees',
        'total_cost',
        'customer_notes',
        'professional_notes',
        'cancellation_reason',
        'booked_at',
        'confirmed_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'pet_id' => 'integer',
        'professional_id' => 'integer',
        'service_id' => 'integer',
        'scheduled_datetime' => 'datetime',
        'duration_minutes' => 'integer',
        'base_cost' => 'decimal:2',
        'additional_fees' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'booked_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the professional (groomer/doctor/nurse) that owns the appointment.
     */
    public function professional()
    {
        return $this->morphTo();
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->whereHas('pet', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('professional', function ($q) use ($search) {
                // Search in the professional's user relationship
                $q->when(method_exists($q->getModel(), 'user'), function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', '%' . $search . '%');
                    });
                });
            });
        })->when($filters['pet_id'] ?? null, function ($query, $petId) {
            $query->where('pet_id', $petId);
        })->when($filters['professional_id'] ?? null, function ($query, $professionalId) {
            $query->where('professional_id', $professionalId);
        })->when($filters['professional_type'] ?? null, function ($query, $professionalType) {
            $query->where('professional_type', $professionalType);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
            $query->where('scheduled_datetime', '>=', $dateFrom);
        })->when($filters['date_to'] ?? null, function ($query, $dateTo) {
            $query->where('scheduled_datetime', '<=', $dateTo);
        })->when($filters['location_type'] ?? null, function ($query, $locationType) {
            $query->where('location_type', $locationType);
        })->when($filters['service_id'] ?? null, function ($query, $serviceId) {
            $query->where('service_id', $serviceId);
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

            // Calculate total cost
            $model->total_cost = $model->base_cost + ($model->additional_fees ?? 0);
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }

            // Recalculate total cost if base_cost or additional_fees change
            if ($model->isDirty('base_cost') || $model->isDirty('additional_fees')) {
                $model->total_cost = $model->base_cost + ($model->additional_fees ?? 0);
            }
        });
    }
}
