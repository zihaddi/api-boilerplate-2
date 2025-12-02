<?php

namespace App\Interfaces\Admin;

interface ServiceRepositoryInterface
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($id, $request);
    public function destroy($id);
    public function getByOrganization($organizationId);
    public function getServicePricing($serviceId);
    public function updateServicePricing($serviceId, $request);
}
