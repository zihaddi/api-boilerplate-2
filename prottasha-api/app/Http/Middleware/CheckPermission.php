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
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    use Access;
    use HttpResponses;
    use Helper;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $action
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $action)
    {
        // Get the authenticated user
        $user = Auth::user();

        return $next($request);

        if (!$user) {
            return $this->error(null, AuthConstants::UNAUTHORIZED, 401, false);
        }
        // Fetch the user's role ID
        $roleId = $user->user_type; // Assuming `role_id` exists in the `users` table

        // Get the current route name
        $routeName = $request->route()->getName();

        $routeData = explode('.', $routeName);
        $routes = '';
        if (!empty($routeData)) {
            $routes = $routeData[0];
        }
        $task = end($routeData);

        // Find the tree entity associated with the route
        $treeEntity = TreeEntity::where('route_location', $routes)->first();


        if (!$treeEntity) {
            return $this->error(null, 'Route not found in permissions', 404, false);
        }
        // Check the user's permissions for the given tree entity and action
        $permission = RolePermission::where('role_id', $roleId)
            ->where('view', $treeEntity->id)
            ->first();

        if (!$permission) {
            return $this->error(null, 'Forbidden: No view permission', 403, false);
        }

        // Check specific action permissions
        if ($action === 'edit' && (!$permission->edit || $permission->edit != $treeEntity->id)) {
            return $this->error(null, 'Forbidden: No edit permission', 403, false);
        }

        if ($action === 'add' && (!$permission->add || $permission->add != $treeEntity->id)) {
            return $this->error(null, 'Forbidden: No add permission', 403, false);
        }

        if ($action === 'delete' && (!$permission->delete || $permission->delete != $treeEntity->id)) {
            return $this->error(null, 'Forbidden: No delete permission', 403, false);
        }

        return $next($request);
    }
}
