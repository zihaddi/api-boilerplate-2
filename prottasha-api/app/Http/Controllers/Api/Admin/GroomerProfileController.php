<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroomerProfile\GroomerProfileStoreRequest;
use App\Http\Requests\Admin\GroomerProfile\GroomerProfileUpdateRequest;
use App\Interfaces\Admin\GroomerProfileRepositoryInterface;
use App\Models\GroomerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GroomerProfileController extends Controller
{
    protected $client;

    public function __construct(GroomerProfileRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store( GroomerProfileStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show( $id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update( $id, GroomerProfileUpdateRequest $request): JsonResponse
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

    public function getByUser( $userId): JsonResponse
    {
        return $this->client->getByUser($userId);
    }
}
