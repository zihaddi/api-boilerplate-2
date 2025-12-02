<?php

namespace App\Interfaces\Admin;

interface PetRepositoryInterface
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($id, $request);
    public function destroy($id);
    public function getByOwner($ownerId);
    public function getPetCategories();
    public function getPetSubcategories($categoryId = null);
    public function getPetBreeds($subcategoryId = null);
}
