<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecurringDonation\RecurringDonationStoreRequest;
use App\Http\Requests\Admin\RecurringDonation\RecurringDonationUpdateRequest;
use App\Interfaces\Admin\RecurringDonationRepositoryInterface;
use App\Models\RecurringDonation;
use Illuminate\Http\Request;

class RecurringDonationController extends Controller
{
    protected $client;

    public function __construct(RecurringDonationRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(RecurringDonation $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(RecurringDonation $obj, RecurringDonationStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(RecurringDonation $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(RecurringDonation $obj, RecurringDonationUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(RecurringDonation $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(RecurringDonation $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
