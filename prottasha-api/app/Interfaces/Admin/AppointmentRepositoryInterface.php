<?php


namespace App\Interfaces\Admin;

interface AppointmentRepositoryInterface
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($id, $request);
    public function destroy($id);
    public function updateStatus($id, $request);
    public function getByPet($petId);
    public function getByProfessional($type, $id); // Replace getByGroomer
    public function getDashboardStats($request);
}
