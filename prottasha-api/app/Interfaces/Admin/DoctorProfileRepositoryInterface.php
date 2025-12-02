<?php


namespace App\Interfaces\Admin;

interface DoctorProfileRepositoryInterface
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($id, $request);
    public function destroy($id);
    public function getByOrganization($organizationId);
    public function getByUser($userId);
}
