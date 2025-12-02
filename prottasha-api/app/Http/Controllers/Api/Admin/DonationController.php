<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Donation\DonationStoreRequest;
use App\Http\Requests\Admin\Donation\DonationUpdateRequest;
use App\Interfaces\Admin\DonationRepositoryInterface;
use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    protected $client;

    public function __construct(DonationRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(Donation $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(Donation $obj, DonationStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(Donation $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(Donation $obj, DonationUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(Donation $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(Donation $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }

    // Business logic methods
    public function approve($id)
    {
        return $this->client->approveDonation($id);
    }

    public function stats(Request $request)
    {
        return $this->client->getDonationStats($request->all());
    }
}
