<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class RolePermission extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'role_permissions';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'id',
        'role_id',
        'view',
        'add',
        'edit',
        'edit_other',
        'delete',
        'delete_other',
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

    public function user_role()
    {
        return $this->belongsTo(User::class, 'user_type');
    }
    public function tree_menu()
    {
        return $this->belongsTo(TreeEntity::class, 'id', 'view');
    }
    public function tree_menu_add()
    {
        return $this->belongsTo(TreeEntity::class, 'id', 'add');
    }
    public function tree_menu_edit()
    {
        return $this->belongsTo(TreeEntity::class, 'id', 'edit');
    }
    public function tree_menu_delete()
    {
        return $this->belongsTo(TreeEntity::class, 'id', 'delete');
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
}
