<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Service\ServiceStoreRequest;
use App\Http\Requests\Admin\Service\ServiceUpdateRequest;
use App\Interfaces\Admin\ServiceRepositoryInterface;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    protected $client;

    public function __construct(ServiceRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store( ServiceStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show( $id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update( $id, ServiceUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy( $id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function getByOrganization( $organizationId): JsonResponse
    {
        return $this->client->getByOrganization($organizationId);
    }

    public function getServicePricing( $serviceId): JsonResponse
    {
        return $this->client->getServicePricing($serviceId);
    }
}
