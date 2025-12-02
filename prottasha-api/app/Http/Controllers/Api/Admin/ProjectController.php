<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Project\ProjectStoreRequest;
use App\Http\Requests\Admin\Project\ProjectUpdateRequest;
use App\Interfaces\Admin\ProjectRepositoryInterface;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $client;

    public function __construct(ProjectRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Project $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Project $obj, ProjectStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Project $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Project $obj, ProjectUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Project $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Project $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
