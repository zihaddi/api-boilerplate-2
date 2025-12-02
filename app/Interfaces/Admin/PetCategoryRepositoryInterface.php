<?php

namespace App\Interfaces\Admin;

interface PetCategoryRepositoryInterface
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($id, $request);
    public function destroy($id);
    public function getActive();
}
