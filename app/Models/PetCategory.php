<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PetCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pet_categories';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function subcategories()
    {
        return $this->hasMany(PetSubcategory::class, 'category_id');
    }

    public function pets()
    {
        return $this->hasMany(Pet::class, 'category_id');
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
