<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\RoleStoreRequest;
use App\Http\Requests\Admin\Role\RoleUpdateRequest;
use App\Interfaces\Admin\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleRepository;

    /**
     * RoleController constructor.
     *
     * @param RoleRepositoryInterface $roleRepository
     */
    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Role $obj, Request $request)
    {
        return $this->roleRepository->index($obj, $request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Role $obj, RoleStoreRequest $request)
    {
        return $this->roleRepository->store($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Role $obj, $id)
    {
        return $this->roleRepository->show($obj, $id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RoleRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Role $obj, RoleUpdateRequest $request, $id)
    {
        return $this->roleRepository->update($obj, $request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $obj, $id)
    {
        $treeEntity = $obj::find($id);
        return $this->roleRepository->destroy($treeEntity, $id);
    }

    /**
     * Restore a soft-deleted resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Role $obj, $id)
    {
        return $this->roleRepository->restore($obj, $id);
    }
}
