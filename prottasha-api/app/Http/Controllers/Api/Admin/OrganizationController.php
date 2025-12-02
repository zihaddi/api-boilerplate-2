<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Organization\OrganizationStoreRequest;
use App\Http\Requests\Admin\Organization\OrganizationUpdateRequest;
use App\Interfaces\Admin\OrganizationRepositoryInterface;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrganizationController extends Controller
{
    protected $client;

    public function __construct(OrganizationRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store( OrganizationStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show( $id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update( $id, OrganizationUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy( $id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function getActive(Organization $obj): JsonResponse
    {
        return $this->client->getActive();
    }
}
