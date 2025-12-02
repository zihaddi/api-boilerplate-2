<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TreeEntity\TreeEntityStoreRequest;
use App\Http\Requests\Admin\TreeEntity\TreeEntityUpdateMenuRequest;
use App\Http\Requests\Admin\TreeEntity\TreeEntityUpdateRequest;
use App\Interfaces\Admin\TreeEntityRepositoryInterface;
use App\Models\RolePermission;
use App\Models\TreeEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreeEntityController extends Controller
{
    protected $treeEntityRepository;

    /**
     * TreeEntityController constructor.
     *
     * @param TreeEntityRepositoryInterface $treeEntityRepository
     */
    public function __construct(TreeEntityRepositoryInterface $treeEntityRepository)
    {
        $this->treeEntityRepository = $treeEntityRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(TreeEntity $obj, Request $request)
    {
        return $this->treeEntityRepository->index($obj, $request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TreeEntityRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TreeEntity $obj, TreeEntityStoreRequest $request)
    {
        return $this->treeEntityRepository->store($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TreeEntity $obj, $id)
    {
        return $this->treeEntityRepository->show($obj, $id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TreeEntityRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TreeEntity $obj, TreeEntityUpdateRequest $request, $id)
    {
        $treeEntity = $obj::find($id);
        return $this->treeEntityRepository->update($treeEntity, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TreeEntity $obj, $id)
    {
        $treeEntity = $obj::find($id);
        return $this->treeEntityRepository->destroy($treeEntity, $id);
    }

    /**
     * Restore a soft-deleted resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(TreeEntity $obj, $id)
    {
        return $this->treeEntityRepository->restore($obj, $id);
    }

    /**
     * Build a menu structure.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buildMenu(TreeEntity $obj)
    {
        return $this->treeEntityRepository->buildmenu($obj);
    }


    public function showmenu(TreeEntity $obj)
    {
        return $this->treeEntityRepository->showmenu($obj);
    }

    /**
     * Update menu structure.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMenu(TreeEntity $obj, TreeEntityUpdateMenuRequest $request)
    {
        return $this->treeEntityRepository->updatemenu($obj, $request->all());
    }

    /**
     * Delete or deactivate a menu.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMenu(TreeEntity $obj, Request $request)
    {
        return $this->treeEntityRepository->deleteMenu($obj, $request);
    }


    public function treemenuNew(TreeEntity $obj)
    {
        return $this->treeEntityRepository->treemenuNew($obj);
    }
}
