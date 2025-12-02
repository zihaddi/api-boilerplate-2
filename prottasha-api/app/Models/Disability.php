<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Disability extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'disabilities';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'name',
        'description',
        'severity_level',
        'status',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'severity_level' => 'string',
        'status' => 'string',
    ];

    // Relationships
    public function userProfiles()
    {
        return $this->hasMany(UserProfile::class);
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

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity_level', $severity);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['name'] ?? null, function ($query, $name) {
            $query->where('name', 'like', '%' . $name . '%');
        })->when($filters['severity_level'] ?? null, function ($query, $severity) {
            $query->where('severity_level', $severity);
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
