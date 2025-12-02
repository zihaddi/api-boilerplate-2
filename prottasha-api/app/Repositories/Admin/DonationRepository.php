<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\Donation\DonationResource;
use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\DonationRepositoryInterface;
use App\Models\ActivityLog;

class DonationRepository extends BaseRepository implements DonationRepositoryInterface
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

    // Index: Retrieve all donations
    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['donor.userProfile', 'project', 'donationTaker.userProfile'])
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
                $responseData = DonationResource::collection($query)->response()->getData();
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

    // Store: Create a new donation
    public function store($obj, $request)
    {
        DB::beginTransaction();
        try {
            $request['created_by'] = Auth::id();

            $donation = $obj::create($request);

            if ($donation) {
                // Log the donation creation
                ActivityLog::logDonationCreated($donation);

                DB::commit();

                $responseData = new DonationResource($donation);
                return $this->success($responseData, Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                DB::rollback();
                return $this->error(null, Constants::STORE, Response::HTTP_BAD_REQUEST, false);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Retrieve a specific donation
    public function show($obj, $id)
    {
        try {
            $donation = $obj::with(['donor.userProfile', 'project', 'donationTaker.userProfile', 'allocations', 'deliveries'])
                ->find($id);

            if ($donation) {
                $responseData = new DonationResource($donation);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Update a specific donation
    public function update($obj, $request, $id)
    {
        DB::beginTransaction();
        try {
            $donation = $obj::find($id);

            if (!$donation) {
                return $this->error(null, Constants::UPDATE, Response::HTTP_NOT_FOUND, false);
            }

            $request['modified_by'] = Auth::id();
            $donation->update($request);

            // Log the donation update
            ActivityLog::logDonationUpdated($donation);

            DB::commit();

            $responseData = new DonationResource($donation);
            return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete a donation
    public function destroy($obj, $id)
    {
        DB::beginTransaction();
        try {
            $donation = $obj::find($id);

            if (!$donation) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $donation->delete();

            // Log the donation deletion
            ActivityLog::logDonationDeleted($donation);

            DB::commit();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted donation
    public function restore($obj, $id)
    {
        DB::beginTransaction();
        try {
            $donation = $obj::withTrashed()->find($id);

            if (!$donation) {
                return $this->error(null, 'Donation not found', Response::HTTP_NOT_FOUND, false);
            }

            $donation->restore();

            // Log the donation restoration
            ActivityLog::logDonationRestored($donation);

            DB::commit();

            $responseData = new DonationResource($donation);
            return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Business Logic Methods

    /**
     * Approve a donation
     */
    public function approveDonation($id)
    {
        DB::beginTransaction();
        try {
            $donation = \App\Models\Donation::find($id);

            if (!$donation) {
                return $this->error(null, 'Donation not found', Response::HTTP_NOT_FOUND, false);
            }

            if ($donation->status !== 'pending') {
                return $this->error(null, 'Only pending donations can be approved', Response::HTTP_BAD_REQUEST, false);
            }

            $donation->update([
                'status' => 'approved',
                'modified_by' => Auth::id()
            ]);

            // Log the approval
            ActivityLog::logDonationUpdated($donation, ['status' => 'pending'], ['status' => 'approved']);

            DB::commit();

            return $this->success(new \App\Http\Resources\Admin\Donation\DonationResource($donation), 'Donation approved successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    /**
     * Get donation statistics
     */
    public function getDonationStats($filters = [])
    {
        try {
            $query = \App\Models\Donation::query();

            // Apply filters
            if (!empty($filters['date_range'])) {
                $query->whereBetween('created_at', [$filters['date_range']['start'], $filters['date_range']['end']]);
            }

            $stats = [
                'total_donations' => $query->count(),
                'total_amount' => $query->sum('amount'),
                'pending_donations' => $query->where('status', 'pending')->count(),
                'approved_donations' => $query->where('status', 'approved')->count(),
                'completed_donations' => $query->where('status', 'completed')->count(),
                'cancelled_donations' => $query->where('status', 'cancelled')->count(),
                'average_donation' => $query->avg('amount'),
            ];

            return $this->success($stats, 'Donation statistics retrieved successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
