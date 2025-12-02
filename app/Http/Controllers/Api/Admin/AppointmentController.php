<?php


namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Appointment\AppointmentStoreRequest;
use App\Http\Requests\Admin\Appointment\AppointmentUpdateRequest;
use App\Interfaces\Admin\AppointmentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    protected $client;

    public function __construct(AppointmentRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->client->index($request);
    }

    public function store(AppointmentStoreRequest $request): JsonResponse
    {
        return $this->client->store($request);
    }

    public function show($id): JsonResponse
    {
        return $this->client->show($id);
    }

    public function update($id, AppointmentUpdateRequest $request): JsonResponse
    {
        return $this->client->update($id, $request);
    }

    public function destroy($id): JsonResponse
    {
        return $this->client->destroy($id);
    }

    public function updateStatus($id, Request $request): JsonResponse
    {
        return $this->client->updateStatus($id, $request);
    }

    public function getByPet($petId): JsonResponse
    {
        return $this->client->getByPet($petId);
    }

    public function getByProfessional($type, $id): JsonResponse
    {
        return $this->client->getByProfessional($type, $id);
    }

    public function getDashboardStats(Request $request): JsonResponse
    {
        return $this->client->getDashboardStats($request);
    }
}
