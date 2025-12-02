<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PetSubcategory\PetSubcategoryStoreRequest;
use App\Http\Requests\Admin\PetSubcategory\PetSubcategoryUpdateRequest;
use App\Interfaces\Admin\PetSubcategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PetSubcategoryController extends Controller
{
    protected $client;

    public function __construct(PetSubcategoryRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store(PetSubcategoryStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show($id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update($id, PetSubcategoryUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy($id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function getByCategory($categoryId): JsonResponse
    {
        return $this->client->getByCategory($categoryId);
    }

    public function getActive(): JsonResponse
    {
        return $this->client->getActive();
    }
}
