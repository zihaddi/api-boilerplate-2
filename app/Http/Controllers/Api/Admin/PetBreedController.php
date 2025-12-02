<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PetBreed\PetBreedStoreRequest;
use App\Http\Requests\Admin\PetBreed\PetBreedUpdateRequest;
use App\Interfaces\Admin\PetBreedRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PetBreedController extends Controller
{
    protected $client;

    public function __construct(PetBreedRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store(PetBreedStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show($id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update($id, PetBreedUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy($id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function getBySubcategory($subcategoryId): JsonResponse
    {
        return $this->client->getBySubcategory($subcategoryId);
    }

    public function getActive(): JsonResponse
    {
        return $this->client->getActive();
    }
}
