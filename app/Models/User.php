<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthenticationLoggable;

    protected $table = 'users';

    protected array $superAdminRoles = ['Super Admin', 'super_admin'];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        "uid",
        "mobile",
        "ccode",
        "email",
        "auth_code",
        "otp_for",
        "is_verify",
        "status",
        "photo",
        "mobile_verified_at",
        "email_verified_at",
        "user_type",
        "password",
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function UserName()
    {
        return $this->hasOne(UserInfo::class, 'user_id')->select('user_id', 'first_name', 'last_name');
    }

    public function UserInfo()
    {
        return $this->hasOne(UserInfo::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'user_type', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isSuperAdmin(): bool
    {
        $role = $this->role;
        return $role && in_array($role->role_name, $this->superAdminRoles);
    }

    public function isAdmin(): bool
    {
        $role = $this->role;
        return $role && in_array($role->role_name, array_merge($this->superAdminRoles, ['Admin']));
    }

    public function hasRole(string $roleName): bool
    {
        $role = $this->role;
        return $role && $role->role_name === $roleName;
    }

    public function hasAnyRole(array $roleNames): bool
    {
        $role = $this->role;
        return $role && in_array($role->role_name, $roleNames);
    }

    public function hasPermission(string $routeName, string $action = 'view'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $cacheKey = "user_{$this->id}_permission_{$routeName}_{$action}";

        return Cache::remember($cacheKey, 300, function () use ($routeName, $action) {
            $treeEntity = TreeEntity::where('route_name', $routeName)
                ->orWhere('route_location', '/' . $routeName)
                ->orWhere('route_location', $routeName)
                ->first();

            if (!$treeEntity) {
                return true;
            }

            $permission = RolePermission::where('role_id', $this->user_type)
                ->where('view', $treeEntity->id)
                ->first();

            if (!$permission) {
                return false;
            }

            return match ($action) {
                'view' => true,
                'add' => (bool) ($permission->add && $permission->add == $treeEntity->id),
                'edit' => (bool) ($permission->edit && $permission->edit == $treeEntity->id),
                'delete' => (bool) ($permission->delete && $permission->delete == $treeEntity->id),
                default => false,
            };
        });
    }

    public function canView(string $routeName): bool
    {
        return $this->hasPermission($routeName, 'view');
    }

    public function canAdd(string $routeName): bool
    {
        return $this->hasPermission($routeName, 'add');
    }

    public function canEdit(string $routeName): bool
    {
        return $this->hasPermission($routeName, 'edit');
    }

    public function canDelete(string $routeName): bool
    {
        return $this->hasPermission($routeName, 'delete');
    }

    public function clearPermissionCache(): void
    {
        $patterns = [
            "user_{$this->id}_permission_*",
        ];

        Cache::flush();
    }

    public function getPhotoAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return $value ? url(Storage::url($value)) : null;
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('id', 'desc');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('mobile', 'like', '%' . $search . '%');
        })->when(isset($filters['role']) && $filters['role'] !== null, function ($query) use ($filters) {
            $query->where('user_type', '=', $filters['role']);
        })->when(isset($filters['status']) && $filters['status'] !== null, function ($query) use ($filters) {
            $query->where('status', '=', strtoupper($filters['status']));
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }

    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('role_name', $roleName);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verify', 1);
    }
}
