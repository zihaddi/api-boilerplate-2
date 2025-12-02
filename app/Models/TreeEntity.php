<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TreeEntity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tree_entities';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'id',
        'pid',
        'node_name',
        'route_name',
        'route_location',
        'icon',
        'status',
        'serials',
        'created_by',
        'modified_by',
        'deleted_at',
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
        return $this->hasMany(RolePermission::class, 'view', 'id');
    }
    public function child()
    {
        $user = Auth::user();
        $profile_id = $user->user_type;
        return $this->hasMany(TreeEntity::class, 'pid', 'id')->with([
            'child' => function ($q)  use ($profile_id) {
                $q->join(
                    DB::raw('( SELECT
                                    view,`add`,`edit`,`delete`
                                FROM
                                    role_permissions
                                WHERE
                                    role_id = ' . $profile_id . ')
                                t1'),
                    function ($join) {
                        $join->on('tree_entities.id', '=', 't1.view');
                        $join->orOn('tree_entities.id', '=', 't1.add');
                        $join->orOn('tree_entities.id', '=', 't1.edit');
                    }
                );
            }
        ])->select('id', 'pid', 'node_name as name', 'route_name as route','route_location', 'icon as icon', 'view', 'add', 'edit', 'delete')->selectRaw("false as is_open")->orderBy('serials');
    }

    public function menus()
    {
        return $this->hasMany(TreeEntity::class, 'pid', 'id')->with('menus')->orderBy('serials')->select('id', 'node_name', 'pid', 'route_name','route_location', 'serials', 'status', 'icon');
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
    public function scopeOrderByName($query) {}
    public function scopeFilter($query, array $filters) {}
}
