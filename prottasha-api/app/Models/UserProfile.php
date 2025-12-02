<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_profiles';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'nid_or_passport',
        'date_of_birth',
        'gender',
        'profile_image_url',
        'address_line_1',
        'address_line_2',
        'postal_code',
        'country_id',
        'division_id',
        'district_id',
        'thana_id',
        'upazila_id',
        'union_id',
        'disability_id',
        'disability_description',
        'emergency_contact_name',
        'emergency_contact_phone',
        'profile_completed_at',
        'verified_at',
        'verified_by',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'date_of_birth' => 'date',
        'country_id' => 'integer',
        'division_id' => 'integer',
        'district_id' => 'integer',
        'thana_id' => 'integer',
        'upazila_id' => 'integer',
        'union_id' => 'integer',
        'disability_id' => 'integer',
        'verified_by' => 'integer',
        'profile_completed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    protected $appends = ['full_name'];

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getProfileImageUrlAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url(Storage::url($value)) : null;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public function disability()
    {
        return $this->belongsTo(Disability::class);
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

    // Scopes
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('profile_completed_at');
    }

    public function scopeByLocation($query, $country = null, $division = null, $district = null, $thana = null, $upazila = null, $union = null)
    {
        return $query->when($country, fn($q) => $q->where('country_id', $country))
            ->when($division, fn($q) => $q->where('division_id', $division))
            ->when($district, fn($q) => $q->where('district_id', $district))
            ->when($thana, fn($q) => $q->where('thana_id', $thana))
            ->when($upazila, fn($q) => $q->where('upazila_id', $upazila))
            ->when($union, fn($q) => $q->where('union_id', $union));
    }

    public function scopeWithDisability($query)
    {
        return $query->whereNotNull('disability_id');
    }

    public function scopeOrderByName($query)
    {
        return $query->orderBy('first_name')->orderBy('last_name');
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['name'] ?? null, function ($query, $name) {
            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', '%' . $name . '%')
                  ->orWhere('last_name', 'like', '%' . $name . '%');
            });
        })->when($filters['country_id'] ?? null, function ($query, $countryId) {
            $query->where('country_id', $countryId);
        })->when($filters['division_id'] ?? null, function ($query, $divisionId) {
            $query->where('division_id', $divisionId);
        })->when($filters['district_id'] ?? null, function ($query, $districtId) {
            $query->where('district_id', $districtId);
        })->when($filters['disability_id'] ?? null, function ($query, $disabilityId) {
            $query->where('disability_id', $disabilityId);
        })->when($filters['verified'] ?? null, function ($query, $verified) {
            if ($verified) {
                $query->whereNotNull('verified_at');
            } else {
                $query->whereNull('verified_at');
            }
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
