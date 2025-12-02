<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\Donation\DonationAllocationResource;
use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\DonationAllocationRepositoryInterface;
use App\Models\ActivityLog;

class DonationAllocationRepository extends BaseRepository implements DonationAllocationRepositoryInterface
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

    // Index: Retrieve all donation allocations
    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['donation.donor', 'allocatedTo.userProfile'])
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
                $responseData = DonationAllocationResource::collection($query)->response()->getData();
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

    // Store: Create a new donation allocation
    public function store($obj, $request)
    {
        DB::beginTransaction();
        try {
            $request['created_by'] = Auth::id();

            $allocation = $obj::create($request);

            if ($allocation) {
                // Log the allocation creation
                ActivityLog::logAllocationCreated($allocation);

                DB::commit();

                $responseData = new DonationAllocationResource($allocation);
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

    // Show: Retrieve a specific allocation
    public function show($obj, $id)
    {
        try {
            $allocation = $obj::with(['donation.donor', 'allocatedTo.userProfile'])
                ->find($id);

            if ($allocation) {
                $responseData = new DonationAllocationResource($allocation);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Update a specific allocation
    public function update($obj, $request, $id)
    {
        DB::beginTransaction();
        try {
            $allocation = $obj::find($id);

            if (!$allocation) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $request['modified_by'] = Auth::id();
            $allocation->update($request);

            // Log the allocation update
            ActivityLog::logAllocationUpdated($allocation);

            DB::commit();

            $responseData = new DonationAllocationResource($allocation);
            return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete an allocation
    public function destroy($obj, $id)
    {
        DB::beginTransaction();
        try {
            $allocation = $obj::find($id);

            if (!$allocation) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $allocation->delete();

            // Log the allocation deletion
            ActivityLog::logAllocationDeleted($allocation);

            DB::commit();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted allocation
    public function restore($obj, $id)
    {
        DB::beginTransaction();
        try {
            $allocation = $obj::withTrashed()->find($id);

            if (!$allocation) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $allocation->restore();

            // Log the allocation restoration
            ActivityLog::logAllocationRestored($allocation);

            DB::commit();

            $responseData = new DonationAllocationResource($allocation);
            return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
