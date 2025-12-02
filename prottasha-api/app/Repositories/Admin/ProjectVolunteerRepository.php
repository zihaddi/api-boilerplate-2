<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\ProjectVolunteer\ProjectVolunteerResource;
use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\ProjectVolunteerRepositoryInterface;
use App\Models\ActivityLog;

class ProjectVolunteerRepository extends BaseRepository implements ProjectVolunteerRepositoryInterface
{
    use HttpResponses;
    use Helper;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all project volunteers
    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['project', 'user.userProfile'])
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
                $responseData = ProjectVolunteerResource::collection($query)->response()->getData();
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

    // Store: Create a new project volunteer
    public function store($obj, $request)
    {
        DB::beginTransaction();
        try {
            $request['created_by'] = Auth::id();

            $volunteer = $obj::create($request);

            if ($volunteer) {
                // Log the volunteer assignment
                ActivityLog::logActivity('created', 'project_volunteer', $volunteer->id);

                DB::commit();

                $responseData = new ProjectVolunteerResource($volunteer);
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

    // Show: Retrieve a specific project volunteer
    public function show($obj, $id)
    {
        try {
            $volunteer = $obj::with(['project', 'user.userProfile'])
                ->find($id);

            if ($volunteer) {
                $responseData = new ProjectVolunteerResource($volunteer);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Update a specific project volunteer
    public function update($obj, $request, $id)
    {
        DB::beginTransaction();
        try {
            $volunteer = $obj::find($id);

            if (!$volunteer) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $request['modified_by'] = Auth::id();
            $volunteer->update($request);

            // Log the volunteer update
            ActivityLog::logActivity('updated', 'project_volunteer', $volunteer->id);

            DB::commit();

            $responseData = new ProjectVolunteerResource($volunteer);
            return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete a project volunteer
    public function destroy($obj, $id)
    {
        DB::beginTransaction();
        try {
            $volunteer = $obj::find($id);

            if (!$volunteer) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $volunteer->delete();

            // Log the volunteer removal
            ActivityLog::logActivity('deleted', 'project_volunteer', $volunteer->id);

            DB::commit();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted project volunteer
    public function restore($obj, $id)
    {
        DB::beginTransaction();
        try {
            $volunteer = $obj::withTrashed()->find($id);

            if (!$volunteer) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $volunteer->restore();

            // Log the volunteer restoration
            ActivityLog::logActivity('restored', 'project_volunteer', $volunteer->id);

            DB::commit();

            $responseData = new ProjectVolunteerResource($volunteer);
            return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
