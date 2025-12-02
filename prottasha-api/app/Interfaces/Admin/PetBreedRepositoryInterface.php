<?php

namespace App\Interfaces\Admin;

interface PetBreedRepositoryInterface
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($id, $request);
    public function destroy($id);
    public function getBySubcategory($subcategoryId);
    public function getActive();
}
