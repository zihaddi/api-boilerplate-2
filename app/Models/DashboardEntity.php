<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardEntity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dashboard_entities';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'id',
        'pid',
        'node_name',
        'slug',
        'icon',
        'status',
        'serials',
        'parents',
        'type',
        'created_by',
        'modified_by',
    ];

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function modified_by()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
    public function permission()
    {
        return $this->hasMany(DashboardPermission::class, 'view', 'id');
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

    public function child()
    {
        $user = Auth::user();
        $profile_id = $user->user_role_id;
        // $profile_id=1;
        return $this->hasMany(TreeEntity::class, 'pid', 'id')->with([
            'child' => function ($q)  use ($profile_id) {
                $q
                    ->join(
                        DB::raw('( SELECT
                                              view
                                            FROM
                                                role_permissions
                                            WHERE
                                                role_id = ' . $profile_id . ')
                                           t1'),
                        function ($join) {
                            $join->on('dashboard_entities.id', '=', 't1.view');
                        }
                    );
            }
        ])->select('id', 'pid', 'node_name as title', 'slug', 'icon as icon', 'view')->orderBy('serials');
    }

    public function menus()
    {
        return $this->hasMany(DashboardEntity::class, 'pid', 'id')->with('menus')->orderBy('serials')->select('id', 'node_name', 'pid', 'slug', 'serials', 'status', 'icon');
    }
    public function scopeOrderByName($query) {}
    public function scopeFilter($query, array $filters) {}
}
