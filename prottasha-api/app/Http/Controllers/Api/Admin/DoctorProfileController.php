<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DoctorProfile\DoctorProfileStoreRequest;
use App\Http\Requests\Admin\DoctorProfile\DoctorProfileUpdateRequest;
use App\Interfaces\Admin\DoctorProfileRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorProfileController extends Controller
{
    protected $client;

    public function __construct(DoctorProfileRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store(DoctorProfileStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show($id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update($id, DoctorProfileUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy($id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function getByOrganization($organizationId): JsonResponse
    {
        return $this->client->getByOrganization($organizationId);
    }

    public function getByUser($userId): JsonResponse
    {
        return $this->client->getByUser($userId);
    }
}
