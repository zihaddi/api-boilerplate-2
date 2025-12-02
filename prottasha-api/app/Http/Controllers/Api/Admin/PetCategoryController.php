<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PetCategory\PetCategoryStoreRequest;
use App\Http\Requests\Admin\PetCategory\PetCategoryUpdateRequest;
use App\Interfaces\Admin\PetCategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PetCategoryController extends Controller
{
    protected $client;

    public function __construct(PetCategoryRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store(PetCategoryStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show($id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update($id, PetCategoryUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy($id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function getActive(): JsonResponse
    {
        return $this->client->getActive();
    }
}
