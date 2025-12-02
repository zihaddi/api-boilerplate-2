<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'services';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'base_price',
        'estimated_duration',
        'category',
        'requires_pet_categories',
        'status',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'base_price' => 'decimal:2',
        'estimated_duration' => 'integer',
        'requires_pet_categories' => 'json',
        'status' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function servicePricing()
    {
        return $this->hasMany(ServicePricing::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
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

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        })->when($filters['organization_id'] ?? null, function ($query, $orgId) {
            $query->where('organization_id', $orgId);
        })->when($filters['category'] ?? null, function ($query, $category) {
            $query->where('category', $category);
        })->when(isset($filters['status']) && $filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', '=', $filters['status']);
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
        });

        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }
        });
    }
}
