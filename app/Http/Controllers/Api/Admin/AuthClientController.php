<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthClient\AuthClientStoreRequest;
use App\Http\Requests\Admin\AuthClient\AuthClientUpdateRequest;
use App\Http\Resources\Admin\AuthClient\AuthClientStoreResource;
use App\Http\Resources\Admin\AuthClient\AuthClientUpdateResource;
use App\Interfaces\Admin\AuthClientRepositoryInterface;
use App\Models\AuthClient;
use Illuminate\Http\Request;

class AuthClientController extends Controller
{
    protected $client;

    public function __construct(AuthClientRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(AuthClient $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(AuthClient $obj, AuthClientStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(AuthClient $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(AuthClient $obj, AuthClientUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(AuthClient $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(AuthClient $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
