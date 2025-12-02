<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MonthlySupportProgram\MonthlySupportProgramStoreRequest;
use App\Http\Requests\Admin\MonthlySupportProgram\MonthlySupportProgramUpdateRequest;
use App\Interfaces\Admin\MonthlySupportProgramRepositoryInterface;
use App\Models\MonthlySupportProgram;
use Illuminate\Http\Request;

class MonthlySupportProgramController extends Controller
{
    protected $client;

    public function __construct(MonthlySupportProgramRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(MonthlySupportProgram $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(MonthlySupportProgram $obj, MonthlySupportProgramStoreRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(MonthlySupportProgram $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(MonthlySupportProgram $obj, MonthlySupportProgramUpdateRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(MonthlySupportProgram $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(MonthlySupportProgram $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }
}
