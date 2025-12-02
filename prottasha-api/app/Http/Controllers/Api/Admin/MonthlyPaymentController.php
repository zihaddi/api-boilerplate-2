<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MonthlyPayment\MonthlyPaymentStoreRequest;
use App\Http\Requests\Admin\MonthlyPayment\MonthlyPaymentUpdateRequest;
use App\Interfaces\Admin\MonthlyPaymentRepositoryInterface;
use App\Models\MonthlyPayment;
use Illuminate\Http\Request;

class MonthlyPaymentController extends Controller
{
    protected $client;

    public function __construct(MonthlyPaymentRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(MonthlyPayment $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(MonthlyPayment $obj, MonthlyPaymentStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(MonthlyPayment $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(MonthlyPayment $obj, MonthlyPaymentUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(MonthlyPayment $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(MonthlyPayment $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
