<?php

namespace App\Interfaces\Admin;

interface TreeEntityRepositoryInterface
{
    /**
     * Retrieve all resources with optional soft-deleted records.
     *
     * @param mixed $obj
     * @param Request $request
     * @return mixed
     */
    public function index($obj, $request);

    /**
     * Create a new resource.
     *
     * @param mixed $obj
     * @param Request $request
     * @return mixed
     */
    public function store($obj, $request);

    /**
     * Display a specific resource.
     *
     * @param mixed $obj
     * @param int $treeEntity
     * @return mixed
     */
    public function show($obj, $treeEntity);

    /**
     * Fully update a resource.
     *
     * @param mixed $obj
     * @param Request $request
     * @return mixed
     */
    public function update($obj, $request);

    /**
     * Partially update a resource.
     *
     * @param mixed $obj
     * @param Request $request
     * @return mixed
     */
    public function patch($obj, $request);

    /**
     * Soft delete a resource.
     *
     * @param mixed $obj
     * @param int $id
     * @return mixed
     */
    public function destroy($obj, $id);

    /**
     * Restore a soft-deleted resource.
     *
     * @param mixed $obj
     * @param int $id
     * @return mixed
     */
    public function restore($obj, $id);

    /**
     * Generate a menu structure based on tree entities and permissions.
     *
     * @param mixed $obj
     * @return mixed
     */
    public function treemenu($obj);

    /**
     * Generate a new menu structure based on tree entities and permissions.
     *
     * @param mixed $obj
     * @return mixed
     */
    public function treemenuNew($obj);

    /**
     * Build a menu structure for tree entities.
     *
     * @param mixed $obj
     * @return mixed
     */
    public function buildmenu($obj);
    public function showmenu($obj);

    /**
     * Update the serial and parent ID (pid) of tree entities.
     *
     * @param mixed $obj
     * @param Request $request
     * @return mixed
     */
    public function updatemenu($obj, $request);

    /**
     * Deactivate or restore a menu resource.
     *
     * @param mixed $obj
     * @param Request $request
     * @return mixed
     */
    public function deleteMenu($obj, $request);
}
