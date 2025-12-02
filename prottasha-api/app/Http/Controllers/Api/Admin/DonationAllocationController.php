<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DonationAllocation\DonationAllocationStoreRequest;
use App\Http\Requests\Admin\DonationAllocation\DonationAllocationUpdateRequest;
use App\Interfaces\Admin\DonationAllocationRepositoryInterface;
use App\Models\DonationAllocation;
use Illuminate\Http\Request;

class DonationAllocationController extends Controller
{
    protected $client;

    public function __construct(DonationAllocationRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(DonationAllocation $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(DonationAllocation $obj, DonationAllocationStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(DonationAllocation $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(DonationAllocation $obj, DonationAllocationUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(DonationAllocation $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(DonationAllocation $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
