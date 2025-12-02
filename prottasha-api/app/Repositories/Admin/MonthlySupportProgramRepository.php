<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\MonthlySupport\MonthlySupportProgramResource;
use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\MonthlySupportProgramRepositoryInterface;
use App\Models\ActivityLog;

class MonthlySupportProgramRepository extends BaseRepository implements MonthlySupportProgramRepositoryInterface
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

    // Index: Retrieve all monthly support programs
    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['donor.userProfile', 'beneficiary.userProfile', 'monthlyPayments'])
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
                $responseData = MonthlySupportProgramResource::collection($query)->response()->getData();
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

    // Store: Create a new monthly support program
    public function store($obj, $request)
    {
        DB::beginTransaction();
        try {
            $request['created_by'] = Auth::id();

            $program = $obj::create($request);

            if ($program) {
                // Log the program creation
                ActivityLog::logMonthlySupportCreated($program);

                DB::commit();

                $responseData = new MonthlySupportProgramResource($program);
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

    // Show: Retrieve a specific program
    public function show($obj, $id)
    {
        try {
            $program = $obj::with(['donor.userProfile', 'beneficiary.userProfile', 'monthlyPayments'])
                ->find($id);

            if ($program) {
                $responseData = new MonthlySupportProgramResource($program);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Update a specific program
    public function update($obj, $request, $id)
    {
        DB::beginTransaction();
        try {
            $program = $obj::find($id);

            if (!$program) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $request['modified_by'] = Auth::id();
            $program->update($request);

            // Log the program update
            ActivityLog::logMonthlySupportUpdated($program);

            DB::commit();

            $responseData = new MonthlySupportProgramResource($program);
            return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete a program
    public function destroy($obj, $id)
    {
        DB::beginTransaction();
        try {
            $program = $obj::find($id);

            if (!$program) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $program->delete();

            // Log the program deletion
            ActivityLog::logMonthlySupportDeleted($program);

            DB::commit();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted program
    public function restore($obj, $id)
    {
        DB::beginTransaction();
        try {
            $program = $obj::withTrashed()->find($id);

            if (!$program) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $program->restore();

            // Log the program restoration
            ActivityLog::logMonthlySupportRestored($program);

            DB::commit();

            $responseData = new MonthlySupportProgramResource($program);
            return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
