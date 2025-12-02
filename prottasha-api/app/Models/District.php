<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class District extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'districts';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'division_id',
        'name',
        'code',
        'status',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'division_id' => 'integer',
        'status' => 'string',
    ];

    // Relationships
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function thanas()
    {
        return $this->hasMany(Thana::class);
    }

    public function userProfiles()
    {
        return $this->hasMany(UserProfile::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrderByName($query)
    {
        return $query->orderBy('name');
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['name'] ?? null, function ($query, $name) {
            $query->where('name', 'like', '%' . $name . '%');
        })->when($filters['division_id'] ?? null, function ($query, $divisionId) {
            $query->where('division_id', $divisionId);
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
