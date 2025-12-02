<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectVolunteer\ProjectVolunteerStoreRequest;
use App\Http\Requests\Admin\ProjectVolunteer\ProjectVolunteerUpdateRequest;
use App\Interfaces\Admin\ProjectVolunteerRepositoryInterface;
use App\Models\ProjectVolunteer;
use Illuminate\Http\Request;

class ProjectVolunteerController extends Controller
{
    protected $client;

    public function __construct(ProjectVolunteerRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(ProjectVolunteer $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(ProjectVolunteer $obj, ProjectVolunteerStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(ProjectVolunteer $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(ProjectVolunteer $obj, ProjectVolunteerUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(ProjectVolunteer $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(ProjectVolunteer $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
