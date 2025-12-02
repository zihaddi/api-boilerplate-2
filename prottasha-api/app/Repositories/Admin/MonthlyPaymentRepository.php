<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\MonthlyPayment\MonthlyPaymentResource;
use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Admin\MonthlyPaymentRepositoryInterface;

class MonthlyPaymentRepository extends BaseRepository implements MonthlyPaymentRepositoryInterface
{
    use HttpResponses;
    use Helper;

    public function __construct() {}

    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['supporter.userProfile', 'program'])
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
                $responseData = MonthlyPaymentResource::collection($query)->response()->getData();
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
            $monthlyPayment = $obj::create($request);

            if ($monthlyPayment) {
                DB::commit();
                $responseData = new MonthlyPaymentResource($monthlyPayment);
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
            $monthlyPayment = $obj::with(['supporter.userProfile', 'program'])->find($id);

            if ($monthlyPayment) {
                $responseData = new MonthlyPaymentResource($monthlyPayment);
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
            $monthlyPayment = $obj::find($id);

            if (!$monthlyPayment) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $request['modified_by'] = Auth::id();
            $monthlyPayment->update($request);

            DB::commit();

            $responseData = new MonthlyPaymentResource($monthlyPayment);
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
            $monthlyPayment = $obj::find($id);

            if (!$monthlyPayment) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $monthlyPayment->delete();
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
            $monthlyPayment = $obj::withTrashed()->find($id);

            if (!$monthlyPayment) {
                return $this->error(null, Constants::NOTFOUND, Response::HTTP_NOT_FOUND, false);
            }

            $monthlyPayment->restore();
            DB::commit();

            $responseData = new MonthlyPaymentResource($monthlyPayment);
            return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
