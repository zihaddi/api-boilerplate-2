<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserProfile\UserProfileStoreRequest;
use App\Http\Requests\Admin\UserProfile\UserProfileUpdateRequest;
use App\Interfaces\Admin\UserProfileRepositoryInterface;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    protected $client;

    public function __construct(UserProfileRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(UserProfile $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(UserProfile $obj, UserProfileStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(UserProfile $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(UserProfile $obj, UserProfileUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(UserProfile $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(UserProfile $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
