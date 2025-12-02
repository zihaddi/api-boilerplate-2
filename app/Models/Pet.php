<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pets';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'owner_id',
        'name',
        'category_id',
        'subcategory_id',
        'breed_id',
        'birthday',
        'weight',
        'sex',
        'current_medications',
        'medication_allergies',
        'health_conditions',
        'special_notes',
        'photo',
        'status',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'owner_id' => 'integer',
        'category_id' => 'integer',
        'subcategory_id' => 'integer',
        'breed_id' => 'integer',
        'birthday' => 'date',
        'weight' => 'decimal:2',
        'current_medications' => 'json',
        'medication_allergies' => 'json',
        'health_conditions' => 'json',
        'status' => 'boolean',
    ];

    public function getPhotoAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url(Storage::url($value)) : null;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category()
    {
        return $this->belongsTo(PetCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(PetSubcategory::class);
    }

    public function breed()
    {
        return $this->belongsTo(PetBreed::class);
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
                    ->orWhere('special_notes', 'like', '%' . $search . '%');
            });
        })->when($filters['owner_id'] ?? null, function ($query, $ownerId) {
            $query->where('owner_id', $ownerId);
        })->when($filters['category_id'] ?? null, function ($query, $categoryId) {
            $query->where('category_id', $categoryId);
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
