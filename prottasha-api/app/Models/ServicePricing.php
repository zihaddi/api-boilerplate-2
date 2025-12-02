<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ServicePricing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_pricing';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'service_id',
        'location_type',
        'price',
        'additional_fees',
        'status',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'service_id' => 'integer',
        'price' => 'decimal:2',
        'additional_fees' => 'decimal:2',
        'status' => 'boolean',
    ];

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

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByLocationtype($query, $locationType)
    {
        return $query->where('location_type', $locationType);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->whereHas('service', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        });

        $query->when($filters['location_type'] ?? null, function ($query, $locationType) {
            $query->where('location_type', $locationType);
        });

        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        });

        $query->when($filters['service_id'] ?? null, function ($query, $serviceId) {
            $query->where('service_id', $serviceId);
        });

        return $query;
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
    }
}
