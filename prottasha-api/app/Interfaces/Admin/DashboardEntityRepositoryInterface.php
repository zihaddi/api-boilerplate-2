<?php

namespace App\Interfaces\Admin;

interface DashboardEntityRepositoryInterface
{
    public function index($obj, $request);
    public function store($obj, $request);
    public function show($obj, $id);
    public function update($obj, $request);
    public function destroy($obj, $id);
}
