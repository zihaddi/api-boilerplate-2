<?php

namespace App\Repositories\Admin;

use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Resources\Admin\TreeEntity\TreeEntityResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Admin\TreeEntityRepositoryInterface;
use App\Models\RolePermission;
use App\Models\TreeEntity;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TreeEntityRepository extends BaseRepository implements TreeEntityRepositoryInterface
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

    public function index($obj, $request)
    {
        try {
            $query = $obj::with('menus')
                ->orderByName()
                ->filter((array)$request);
            $query = $query->when(
                isset($request['paginate']) && $request['paginate'] == true,
                function ($query) use ($request) {
                    return $query->paginate($request['length'] ?? $request['length'] = 15)->withQueryString();
                },
                function ($query) {
                    return $query->get();
                }
            );
            if ($query) {
                $responseData = TreeEntityResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Add: Create a new resource
    public function store($obj, $request)
    {
        try {
            $treeEntity = $obj::create([
                'pid' => $request->pid,
                'node_name' => $request->node_name,
                'route_name' => $request->route_name,
                'route_location' => $request->route_location,
                'icon' => $request->icon,
                'status' => $request->status,
                'serials' => $request->serials
            ]);
            if ($treeEntity) {
                return $this->success(new TreeEntityResource($treeEntity), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific resource
    public function show($obj, $treeEntity)
    {
        try {
            $treeEntity = $obj::find($treeEntity);

            if ($treeEntity) {
                return $this->success(new TreeEntityResource($treeEntity), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
        return new TreeEntityResource($treeEntity);
    }

    // Update: Fully update a resource
    public function update($obj, $request)
    {
        try {

            $obj->update(
                [
                    'pid' => $request->pid,
                    'node_name' => $request->node_name,
                    'route_name' => $request->route_name,
                    'route_location' => $request->route_location,
                    'icon' => $request->icon,
                    'status' => $request->status,
                    'serials' => $request->serials,
                ]
            );
            if ($obj) {
                return $this->success(new TreeEntityResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILUPDATE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Patch: Partially update a resource
    public function patch($obj, $request)
    {
        try {
            $obj->update($request);
            if ($obj) {
                return $this->success(new TreeEntityResource($obj), Constants::PATCH, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILPATCH, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Delete: Soft delete a resource
    public function destroy($obj, $id)
    {
        try {
            $obj->delete();
            if ($obj) {
                return $this->success(null, Constants::DESTROY, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILDESTROY, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted resource
    public function restore($obj, $id)
    {
        try {
            $treeEntity = $obj::withTrashed()->find($id);
            if ($treeEntity) {
                $treeEntity->restore();
                if ($treeEntity) {
                    return $this->success(new TreeEntityResource($treeEntity), Constants::RESTORE, Response::HTTP_CREATED, true);
                } else {
                    return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
    /**
     * Create menu  from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function treemenu($obj)
    {
        $user = Auth::user();
        $profile_id = $user->role_id;
        $treeentrys = $obj::join(
            DB::raw('( SELECT
                        view,`add`,`edit`,`delete`,`edit_other `,`delete_other`
                    FROM
                        role_permissions
                    WHERE
                        role_id = ' . $profile_id . ')
                               t1'),
            function ($join) {
                $join->on('tree_entities.id', '=', 't1.view');
            }
        )
            ->where('pid', 0)
            ->where('status', '=', 1)
            ->orderBy('serials')
            ->with([
                'children' => function ($q)  use ($profile_id) {
                    $q
                        ->join(
                            DB::s('( SELECT
                                view,`add`,`edit`,`delete`,`edit_other `,`delete_other`
                                            FROM
                                                role_permissions
                                            WHERE
                                                role_id = ' . $profile_id . ')
                                           t1'),
                            function ($join) {
                                $join->on('tree_entities.id', '=', 't1.view');
                                $join->orOn('tree_entities.id', '=', 't1.add');
                                $join->orOn('tree_entities.id', '=', 't1.edit');
                                $join->orOn('tree_entities.id', '=', 't1.edit_other');
                                $join->orOn('tree_entities.id', '=', 't1.delete_other');
                            }
                        );
                }
            ])
            ->get();
    }
    /**
     * Create menu new  from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function treemenuNew($obj)
    {

        try {
            $user = Auth::user();
            if ($user != '') {
                $profile_id = $user->user_type;
                $treeentrys = $obj::join(
                    DB::raw('( SELECT
                                    view,`add`,`edit`,`delete`
                                    FROM
                                        role_permissions
                                    WHERE
                                        role_id  = ' . $profile_id . ')
                                   t1'),
                    function ($join) {
                        $join->on('tree_entities.id', '=', 't1.view');
                    }
                )
                    ->select('id', 'pid', 'node_name as name', 'route_name as route','route_location', 'icon as icon', 'view', 'add', 'edit', 'delete')
                    ->selectRaw('false as showChild, false as is_open')
                    ->where('pid', 0)
                    ->where('status', '=', 1)
                    ->orderBy('serials')
                    ->with([
                        'child' => function ($q)  use ($profile_id) {
                            $q
                                ->join(
                                    DB::raw('( SELECT
                                                    view,`add`,`edit`,`delete`
                                                FROM
                                                    role_permissions
                                                WHERE
                                                    role_id  = ' . $profile_id . ')
                                               t1'),
                                    function ($join) {
                                        $join->on('tree_entities.id', '=', 't1.view');
                                        $join->orOn('tree_entities.id', '=', 't1.add');
                                        $join->orOn('tree_entities.id', '=', 't1.edit');
                                    }
                                );
                        }
                    ])
                    ->get();
                if ($treeentrys) {
                    return $this->success($treeentrys, Constants::GETALL, Response::HTTP_OK, true);
                } else {
                    return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error(null, AuthConstants::PERMISSION, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }


    /**
     * @return \Illuminate\Http\Response
     */
    public function  buildmenu($obj)
    {

        try {
            $treeentrys = $obj::with('menus')
                ->where('pid', 0)
                ->orderBy('serials')->select('id', 'node_name', 'pid', 'route_name','route_location',  'serials', 'status', 'icon')->get();
            if ($treeentrys) {
                $responseData = TreeEntityResource::collection($treeentrys)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }




    public function  showmenu($obj)
    {

        try {
            $treeentrys = $obj::with('menus')
                ->where('pid', 0)
                ->orderBy('serials')->select('id', 'node_name', 'pid', 'route_name','route_location',  'serials', 'status', 'icon')->get();
            if ($treeentrys) {
                $responseData = TreeEntityResource::collection($treeentrys)->response()->getData();
                $responseData = (array)$responseData;
                // $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                // $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error(1, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }



    /**
     * Update serial and pid resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatemenu($obj, $request)
    {
        try {
            $jdata = $request;
            $readbleArray = $this->parseJsonArray($jdata, $pid = 0);
            $i = 0;
            foreach ($readbleArray as $row) {
                $i++;
                $treeentry = $obj::find($row['id']);
                if ($treeentry) {
                    $treeentry->pid = $row['pid'] ?: 0;
                    $treeentry->serials = $i;
                    $treeentry->save();
                }
            }
            return $this->success(null, Constants::UPDATE, Response::HTTP_CREATED, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    /**
     * Deactivate or restore resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteMenu($obj, $request)
    {
        try {
            $data = $this->recursiveDelete($request->id, $request->status);
            if ($data) {
                return $this->success(null, Constants::UPDATE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILUPDATE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
