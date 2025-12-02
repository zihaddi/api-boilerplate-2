<?php

namespace App\Repositories;

use App\Models\RolePermission;
use App\Models\TreeEntity;
use Illuminate\Support\Facades\Auth;

abstract class BaseRepository
{
    /**
     * Get user permissions for the current route
     * @param string|null $routeName Custom route name if different from the current route
     * @return object
     */
    protected function getUserPermissions(?string $routeName = null): object
    {
        // If no route name provided, try to get it from the current route
        if (!$routeName) {
            $currentRoute = request()->route()->getName();
            $routeParts = explode('.', $currentRoute);
            $routeName = $routeParts[0] ?? '';
        }

        $treeEntity = TreeEntity::where('route_location', $routeName)->first();
        if (!$treeEntity) {
            return (object)[
                'view' => 1,
                'add' => 1,
                'edit' => 1,
                'delete' => 1
            ];
        }

        $roleId = Auth::user()->user_type;
        $permission = RolePermission::where('role_id', $roleId)
            ->where('view', $treeEntity->id)
            ->first();

        return (object)[
            'view' => $permission ? $permission->view > 0 : false,
            'add' => $permission ? $permission->add > 0 : false,
            'edit' => $permission ? $permission->edit > 0 : false,
            'delete' => $permission ? $permission->delete > 0 : false
        ];
    }

    /**
     * Add permissions to response data
     * @param array|object $responseData
     * @param string|null $routeName
     * @return array
     */
    protected function addPermissionsToResponse($responseData, ?string $routeName = null): array
    {
        if (is_object($responseData)) {
            $responseData = json_decode(json_encode($responseData), true);
        }

        $responseData['permissions'] = $this->getUserPermissions($routeName);
        return $responseData;
    }
}
