<?php

namespace App\Repositories\Admin;

use App\Constants\Constants;
use App\Http\Resources\Admin\AuthClient\AuthClientResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Admin\AuthClientRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthClientRepository extends BaseRepository implements AuthClientRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all clients
    public function index($obj, $request)
    {
        try {
            $query = $obj::query()
                ->orderByName()
                ->filter((array)$request);
            $query = $query->when(
                isset($request['paginate']) && $request['paginate'] == true,
                function ($query) use ($request) {
                    return $query->paginate($request['length'] ?? $request['length'] = 15)->withQueryString();
                },
                function ($query) {
                    return $query->get();
                }
            );
            if ($query) {
                $data = AuthClientResource::collection($query)->response()->getData();
                $data = (array)$data;
                $data['permissions'] = $this->getUserPermissions();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Store: Register a new client
    public function store($obj, $request)
    {
        try {
            $data = $request;
            $data['password'] = Hash::make($data['password']); // Hash the password
            $client = $obj::create($data);
            if ($client) {
                return $this->success(new AuthClientResource($client), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific client
    public function show($obj, $id)
    {
        try {
            $client = $obj::findOrFail($id);
            if ($client) {
                return $this->success(new AuthClientResource($client), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Fully update a client's details
    public function update($obj, $request, $id)
    {
        try {
            $obj = $obj::find($id);
            if ($obj) {
                $data = $request;
                if (isset($data['password'])) {
                    $data['password'] = Hash::make($data['password']); // Hash the password if updated
                }
                $updated = $obj->update($data);
                if ($updated) {
                    return $this->success(new AuthClientResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
                } else {
                    return $this->error(null, Constants::FAILUPDATE, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Delete: Soft delete a client
    public function destroy($obj, $id)
    {
        try {
            $obj = $obj::find($id);
            if ($obj) {
                $deleted = $obj->delete();
                if ($deleted) {
                    return $this->success(null, Constants::DESTROY, Response::HTTP_CREATED, true);
                } else {
                    return $this->error(null, Constants::FAILDESTROY, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function restore($obj, $id)
    {
        try {
            $obj = $obj::withTrashed()->find($id);
            if ($obj) {
                if ($obj) {
                    $obj->restore();
                    return $this->success(new AuthClientResource($obj), Constants::RESTORE, Response::HTTP_CREATED, true);
                } else {
                    return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
