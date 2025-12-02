<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\Donation\DonationDeliveryResource;
use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\DonationDeliveryRepositoryInterface;
use App\Models\ActivityLog;

class DonationDeliveryRepository extends BaseRepository implements DonationDeliveryRepositoryInterface
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

    // Index: Retrieve all donation deliveries
    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['donation.donor', 'deliveredTo.userProfile'])
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
                $responseData = DonationDeliveryResource::collection($query)->response()->getData();
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

    // Store: Create a new delivery
    public function store($obj, $request)
    {
        DB::beginTransaction();
        try {
            $request['created_by'] = Auth::id();

            $delivery = $obj::create($request);

            if ($delivery) {
                // Log the delivery creation
                ActivityLog::logDeliveryCreated($delivery);

                DB::commit();

                $responseData = new DonationDeliveryResource($delivery);
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

    // Show: Retrieve a specific delivery
    public function show($obj, $id)
    {
        try {
            $delivery = $obj::with(['donation.donor', 'deliveredTo.userProfile'])
                ->find($id);

            if ($delivery) {
                $responseData = new DonationDeliveryResource($delivery);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Update a specific delivery
    public function update($obj, $request, $id)
    {
        DB::beginTransaction();
        try {
            $delivery = $obj::find($id);

            if (!$delivery) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $request['modified_by'] = Auth::id();
            $delivery->update($request);

            // Log the delivery update
            ActivityLog::logDeliveryUpdated($delivery);

            DB::commit();

            $responseData = new DonationDeliveryResource($delivery);
            return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete a delivery
    public function destroy($obj, $id)
    {
        DB::beginTransaction();
        try {
            $delivery = $obj::find($id);

            if (!$delivery) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $delivery->delete();

            // Log the delivery deletion
            ActivityLog::logDeliveryDeleted($delivery);

            DB::commit();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted delivery
    public function restore($obj, $id)
    {
        DB::beginTransaction();
        try {
            $delivery = $obj::withTrashed()->find($id);

            if (!$delivery) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $delivery->restore();

            // Log the delivery restoration
            ActivityLog::logDeliveryRestored($delivery);

            DB::commit();

            $responseData = new DonationDeliveryResource($delivery);
            return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
