<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserInfo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'user_infos';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        "user_id",
        "first_name",
        "middle_name",
        "last_name",
        "photo",
        "dob",
        "religion_id",
        "gender",
        "occupation",
        "nationality_id",
        "vulnerability_info",
        "pre_country",
        "pre_srteet_address",
        "pre_city",
        "pre_provience",
        "pre_zip",
        "same_as_present_address",
        "per_country",
        "per_srteet_address",
        "per_city",
        "per_provience",
        "per_zip",
        "deleted_at"
    ];

    public function getPhotoAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url(Storage::url($value)) : null;
    }




    protected static function boot()
    {

        parent::boot();

        // updating created_by and modified_by when model is created
        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::user()->id;
            }
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::user()->id;
            }
        });

        // updating modified_by when model is updated
        static::updating(function ($model) {
            if (!$model->isDirty('modified_by')) {
                $model->modified_by = Auth::user()->id;
            }
        });
    }


    public function scopeOrderByName($query)
    {
        $query->orderBy('first_name');
    }

    public function scopeFilter($query, array $filters, $permission)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('first_name', 'like', '%' . $search . '%');
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', '=', $status);
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        })->when($permission->view_others <= 0, function ($query) {
            $query->where('created_by', Auth::user()->id);
        });
    }
}
