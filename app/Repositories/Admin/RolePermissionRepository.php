<?php

namespace App\Repositories\Admin;

use App\Constants\Constants;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Admin\RolePermissionRepositoryInterface;
use App\Models\RolePermission;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionRepository extends BaseRepository implements RolePermissionRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all role permissions
    public function index($obj, $request)
    {
        try {
            $query = $obj::query();
            if ($query) {
                $responseData = ['data' => $query->get()];
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show the form for editing the specified resource
    public function show($id)
    {
        try {
            $profilePermission = DB::select("
                SELECT
                    id,
                    pid,
                    node_name,
                    (SELECT COUNT(pid)
                     FROM tree_entities AS test
                     WHERE test.pid = treeNode.id
                     AND test.node_name <> '') AS haschild,
                    id as `view`,
                    id as `add`,
                    id as `edit`,
                    id as `delete`,
                    per.*
                FROM
                    `tree_entities` as treeNode
                    LEFT JOIN (
                        SELECT view AS sid,
                               IFNULL(`view`, 0) AS `viewP`,
                               IFNULL(`add`, 0) AS `addP`,
                               IFNULL(`edit`, 0) AS `editP`,
                               IFNULL(`delete`, 0) AS `deleteP`
                        FROM role_permissions
                        WHERE role_id = ?
                    ) AS per ON per.sid = treeNode.id
                WHERE treeNode.`status` = 1
                ORDER BY treeNode.serials
            ", [$id]);

            return $this->success($profilePermission, Constants::SHOW, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update the specified resource in storage
    public function pupdate($request, $id)
    {
        try {
            DB::table('role_permissions')
                ->where('role_id', $id)
                ->delete();
            foreach ($request->all() as $value) {
                $pp = RolePermission::create([
                    "role_id" => $id,
                    "view" => $value['viewP'] ?? 0,
                    "add" => $value['addP'] ?? 0,
                    "edit" => $value['editP'] ?? 0,
                    "delete" => $value['deleteP'] ?? 0,
                ]);
            }

            return $this->success(null, Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
