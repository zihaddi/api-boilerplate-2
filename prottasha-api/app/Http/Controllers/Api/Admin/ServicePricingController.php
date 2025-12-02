<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServicePricing\ServicePricingStoreRequest;
use App\Http\Requests\Admin\ServicePricing\ServicePricingUpdateRequest;
use App\Http\Requests\Admin\ServicePricing\ServicePricingBulkUpdateRequest;
use App\Interfaces\Admin\ServicePricingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServicePricingController extends Controller
{
    protected $client;

    public function __construct(ServicePricingRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {

        return $this->client->index($request);
    }

    public function store(ServicePricingStoreRequest $request): JsonResponse
    {

        return $this->client->store($request);
    }

    public function show($id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update($id, ServicePricingUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy($id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function getByService($serviceId): JsonResponse
    {
        return $this->client->getByService($serviceId);
    }

    public function getByServiceAndCategory($serviceId, $categoryId): JsonResponse
    {
        return $this->client->getByServiceAndCategory($serviceId, $categoryId);
    }

    // public function bulkUpdate(ServicePricingBulkUpdateRequest $request): JsonResponse
    // {
    //     return $this->client->bulkUpdate($request);
    // }
}
