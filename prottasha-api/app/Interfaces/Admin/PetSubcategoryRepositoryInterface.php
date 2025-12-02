<?php

namespace App\Interfaces\Admin;

interface PetSubcategoryRepositoryInterface
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($id, $request);
    public function destroy($id);
    public function getByCategory($categoryId);
    public function getActive();
}
