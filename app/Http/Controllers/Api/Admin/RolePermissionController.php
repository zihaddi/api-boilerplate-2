<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RolePermission\RolePermissionUpdateRequest;
use App\Interfaces\Admin\RolePermissionRepositoryInterface;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    protected $rolePermissionRepository;

    /**
     * RoleController constructor.
     *
     * @param RolePermissionRepositoryInterface $rolePermissionRepository
     */
    public function __construct(RolePermissionRepositoryInterface $rolePermissionRepository)
    {
        $this->rolePermissionRepository = $rolePermissionRepository;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->rolePermissionRepository->show($id);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param RoleRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function pupdate(RolePermissionUpdateRequest $request, $id)
    {
        return $this->rolePermissionRepository->pupdate($request, $id);
    }
}
