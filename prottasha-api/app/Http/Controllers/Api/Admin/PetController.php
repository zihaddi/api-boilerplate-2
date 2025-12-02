<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Pet\PetStoreRequest;
use App\Http\Requests\Admin\Pet\PetUpdateRequest;
use App\Interfaces\Admin\PetRepositoryInterface;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PetController extends Controller
{
    protected $client;

    public function __construct(PetRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store( PetStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show( $id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update( $id, PetUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy( $id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function getByOwner( $ownerId): JsonResponse
    {
        return $this->client->getByOwner($ownerId);
    }

    public function getPetCategories(Pet $obj): JsonResponse
    {
        return $this->client->getPetCategories();
    }

    public function getPetSubcategories( Request $request): JsonResponse
    {
        return $this->client->getPetSubcategories($request->category_id);
    }

    public function getPetBreeds( Request $request): JsonResponse
    {
        return $this->client->getPetBreeds($request->subcategory_id);
    }
}
