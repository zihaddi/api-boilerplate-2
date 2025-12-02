<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\Project\ProjectResource;
use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Http\Traits\FileSetup;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\ProjectRepositoryInterface;
use App\Models\ActivityLog;

class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    use HttpResponses;
    use Helper;
    use FileSetup;

    protected $image_target_path = 'images/projects';

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all projects
    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['category', 'district', 'thana', 'donations', 'volunteers'])
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
                $responseData = ProjectResource::collection($query)->response()->getData();
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

    // Store: Create a new project
    public function store($obj, $request)
    {
        DB::beginTransaction();
        try {
            $request['created_by'] = Auth::id();

            // Handle image upload if present
            if (isset($request['image']) && $request['image']) {
                $image_path = $this->base64ToImage($request['image'], $this->image_target_path);
                $request['image'] = $image_path;
            }

            $project = $obj::create($request);

            if ($project) {
                // Log the project creation
                ActivityLog::logProjectCreated($project);

                DB::commit();

                $responseData = new ProjectResource($project);
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

    // Show: Retrieve a specific project
    public function show($obj, $id)
    {
        try {
            $project = $obj::with(['category', 'district', 'thana', 'donations.donor', 'volunteers.user'])
                ->find($id);

            if ($project) {
                $responseData = new ProjectResource($project);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Update a specific project
    public function update($obj, $request, $id)
    {
        DB::beginTransaction();
        try {
            $project = $obj::find($id);

            if (!$project) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $request['modified_by'] = Auth::id();

            // Handle image upload if present
            if (isset($request['image']) && $request['image']) {
                // Delete old image if exists
                if ($project->image) {
                    $this->deleteImage($project->image);
                }
                $image_path = $this->base64ToImage($request['image'], $this->image_target_path);
                $request['image'] = $image_path;
            }

            $project->update($request);

            // Log the project update
            ActivityLog::logProjectUpdated($project);

            DB::commit();

            $responseData = new ProjectResource($project);
            return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete a project
    public function destroy($obj, $id)
    {
        DB::beginTransaction();
        try {
            $project = $obj::find($id);

            if (!$project) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $project->delete();

            // Log the project deletion
            ActivityLog::logProjectDeleted($project);

            DB::commit();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted project
    public function restore($obj, $id)
    {
        DB::beginTransaction();
        try {
            $project = $obj::withTrashed()->find($id);

            if (!$project) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $project->restore();

            // Log the project restoration
            ActivityLog::logProjectRestored($project);

            DB::commit();

            $responseData = new ProjectResource($project);
            return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
