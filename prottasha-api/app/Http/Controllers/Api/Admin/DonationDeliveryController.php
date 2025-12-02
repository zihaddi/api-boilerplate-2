<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DonationDelivery\DonationDeliveryStoreRequest;
use App\Http\Requests\Admin\DonationDelivery\DonationDeliveryUpdateRequest;
use App\Interfaces\Admin\DonationDeliveryRepositoryInterface;
use App\Models\DonationDelivery;
use Illuminate\Http\Request;

class DonationDeliveryController extends Controller
{
    protected $client;

    public function __construct(DonationDeliveryRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(DonationDelivery $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(DonationDelivery $obj, DonationDeliveryStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(DonationDelivery $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(DonationDelivery $obj, DonationDeliveryUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(DonationDelivery $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(DonationDelivery $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
