<?php

namespace App\Http\Middleware;

use App\Constants\Constants;
use App\Constants\AuthConstants;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RolePermission;
use App\Models\TreeEntity;
use App\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    use Access;
    use HttpResponses;
    use Helper;

    protected array $superAdminRoles = ['Super Admin', 'super_admin'];

    protected bool $failClosed = true;

    public function handle(Request $request, Closure $next, $action = 'view')
    {
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, AuthConstants::UNAUTHORIZED, 401, false);
        }

        $roleId = $user->user_type;

        $role = Role::find($roleId);
        if ($role && in_array($role->role_name, $this->superAdminRoles)) {
            return $next($request);
        }

        $routeName = $request->route()->getName();
        if (!$routeName) {
            if ($this->failClosed) {
                return $this->error(null, 'Forbidden: Route not configured for authorization', 403, false);
            }
            return $next($request);
        }

        $treeEntity = $this->findTreeEntity($routeName);

        if (!$treeEntity) {
            if ($this->failClosed) {
                return $this->error(null, 'Forbidden: No permission mapping for this route', 403, false);
            }
            return $next($request);
        }

        $permission = RolePermission::where('role_id', $roleId)
            ->where('view', $treeEntity->id)
            ->first();

        if (!$permission) {
            return $this->error(null, 'Forbidden: No access permission for this resource', 403, false);
        }

        if (!$this->checkActionPermission($permission, $treeEntity->id, $action)) {
            return $this->error(null, "Forbidden: No {$action} permission", 403, false);
        }

        return $next($request);
    }

    protected function findTreeEntity(string $routeName): ?TreeEntity
    {
        $entity = TreeEntity::where('route_name', $routeName)->first();
        if ($entity) {
            return $entity;
        }

        $routeParts = explode('.', $routeName);
        for ($i = count($routeParts) - 1; $i >= 1; $i--) {
            $partialRoute = implode('.', array_slice($routeParts, 0, $i));
            $entity = TreeEntity::where('route_name', $partialRoute)->first();
            if ($entity) {
                return $entity;
            }
        }

        $routePrefix = $routeParts[0] ?? '';
        return TreeEntity::where('route_name', $routePrefix)
            ->orWhere('route_location', '/' . $routePrefix)
            ->orWhere('route_location', $routePrefix)
            ->first();
    }

    protected function checkActionPermission($permission, int $entityId, string $action): bool
    {
        switch ($action) {
            case 'view':
                return true;
            case 'add':
                return $this->hasPermissionValue($permission->add, $entityId);
            case 'edit':
                return $this->hasPermissionValue($permission->edit, $entityId);
            case 'delete':
                return $this->hasPermissionValue($permission->delete, $entityId);
            default:
                return false;
        }
    }

    protected function hasPermissionValue($permissionValue, int $entityId): bool
    {
        if (is_bool($permissionValue)) {
            return $permissionValue;
        }
        if (is_numeric($permissionValue)) {
            return (int) $permissionValue === $entityId || (int) $permissionValue === 1;
        }
        return (bool) $permissionValue;
    }

    public function checkUserPermission($user, string $routeName, string $action = 'view'): bool
    {
        if (!$user) {
            return false;
        }

        $roleId = $user->user_type;
        $role = Role::find($roleId);
        
        if ($role && in_array($role->role_name, $this->superAdminRoles)) {
            return true;
        }

        $treeEntity = $this->findTreeEntity($routeName);

        if (!$treeEntity) {
            return !$this->failClosed;
        }

        $permission = RolePermission::where('role_id', $roleId)
            ->where('view', $treeEntity->id)
            ->first();

        if (!$permission) {
            return false;
        }

        return $this->checkActionPermission($permission, $treeEntity->id, $action);
    }
}
