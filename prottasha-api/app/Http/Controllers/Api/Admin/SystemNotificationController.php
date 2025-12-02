<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SystemNotification\SystemNotificationStoreRequest;
use App\Http\Requests\Admin\SystemNotification\SystemNotificationUpdateRequest;
use App\Interfaces\Admin\SystemNotificationRepositoryInterface;
use App\Models\SystemNotification;
use Illuminate\Http\Request;

class SystemNotificationController extends Controller
{
    protected $client;

    public function __construct(SystemNotificationRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(SystemNotification $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(SystemNotification $obj, SystemNotificationStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(SystemNotification $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(SystemNotification $obj, SystemNotificationUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(SystemNotification $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(SystemNotification $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
