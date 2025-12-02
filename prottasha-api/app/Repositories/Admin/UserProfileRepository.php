<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\UserProfile\UserProfileResource;
use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\UserProfileRepositoryInterface;

class UserProfileRepository extends BaseRepository implements UserProfileRepositoryInterface
{
    use HttpResponses;
    use Helper;

    public function __construct() {}

    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['user', 'country', 'division', 'district', 'thana', 'upazila', 'union'])
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
                $responseData = UserProfileResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function store($obj, $request)
    {
        DB::beginTransaction();
        try {
            $request['created_by'] = Auth::id();
            $userProfile = $obj::create($request);

            if ($userProfile) {
                DB::commit();
                $responseData = new UserProfileResource($userProfile);
                return $this->success($responseData, Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                DB::rollback();
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_BAD_REQUEST, false);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show($obj, $id)
    {
        try {
            $userProfile = $obj::with(['user', 'country', 'division', 'district', 'thana', 'upazila', 'union'])
                ->find($id);

            if ($userProfile) {
                $responseData = new UserProfileResource($userProfile);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function update($obj, $request, $id)
    {
        DB::beginTransaction();
        try {
            $userProfile = $obj::find($id);

            if (!$userProfile) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $request['modified_by'] = Auth::id();
            $userProfile->update($request);

            DB::commit();

            $responseData = new UserProfileResource($userProfile);
            return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function destroy($obj, $id)
    {
        DB::beginTransaction();
        try {
            $userProfile = $obj::find($id);

            if (!$userProfile) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $userProfile->delete();
            DB::commit();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function restore($obj, $id)
    {
        DB::beginTransaction();
        try {
            $userProfile = $obj::withTrashed()->find($id);

            if (!$userProfile) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $userProfile->restore();
            DB::commit();

            $responseData = new UserProfileResource($userProfile);
            return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
