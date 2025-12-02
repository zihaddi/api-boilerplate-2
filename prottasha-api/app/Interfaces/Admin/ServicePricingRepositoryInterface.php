<?php

namespace App\Interfaces\Admin;

interface ServicePricingRepositoryInterface
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($id, $request);
    public function destroy($id);
    public function getByService($serviceId);
    public function getByServiceAndCategory($serviceId, $categoryId);
    public function bulkUpdate($request);
}
