<?php

namespace App\Interfaces\Admin;

interface RolePermissionRepositoryInterface
{
    public function show($id);
    public function pupdate($request, $id);
}
