<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\UserStoreRequest;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Interfaces\Admin\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $client;

    public function __construct(UserRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(User $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(User $obj, UserStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(User $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(User $obj, UserUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(User $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(User $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
