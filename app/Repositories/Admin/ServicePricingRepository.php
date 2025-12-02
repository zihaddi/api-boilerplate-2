<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\ServicePricingRepositoryInterface;
use App\Models\ServicePricing;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ServicePricingRepository implements ServicePricingRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = ServicePricing::with(['service', 'createdBy', 'modifiedBy'])
                ->whereNull('deleted_at');

            if ($request->has('service_id')) {
                $query->where('service_id', $request->service_id);
            }

            if ($request->has('location_type')) {
                $query->where('location_type', $request->location_type);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('service', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('per_page')) {
                $pricings = $query->paginate($request->per_page);
            } else {
                $pricings = $query->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Service pricing retrieved successfully',
                'data' => $pricings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $pricingData = $request->only([
                'service_id',
                'location_type',
                'price',
                'additional_fees',
                'status'
            ]);

            // Add audit fields
            $pricingData['created_by'] = Auth::id();
            $pricingData['created_at'] = now();

            // Check if pricing already exists for this service/location_type combination
            $existingPricing = ServicePricing::where('service_id', $pricingData['service_id'])
                ->where('location_type', $pricingData['location_type'])
                ->whereNull('deleted_at')
                ->first();

            if ($existingPricing) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pricing already exists for this service and location type combination',
                    'data' => null
                ], 400);
            }

            $pricing = ServicePricing::create($pricingData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service pricing created successfully',
                'data' => $pricing->load(['service', 'createdBy'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $pricing = ServicePricing::with(['service', 'createdBy', 'modifiedBy'])
                ->whereNull('deleted_at')
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Service pricing retrieved successfully',
                'data' => $pricing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $pricing = ServicePricing::whereNull('deleted_at')->findOrFail($id);

            $pricingData = $request->only([
                'service_id',
                'location_type',
                'price',
                'additional_fees',
                'status'
            ]);

            // Add audit fields
            $pricingData['modified_by'] = Auth::id();
            $pricingData['updated_at'] = now();

            // Check if pricing already exists for this service/location_type combination (excluding current record)
            if (isset($pricingData['service_id']) && isset($pricingData['location_type'])) {
                $existingPricing = ServicePricing::where('service_id', $pricingData['service_id'])
                    ->where('location_type', $pricingData['location_type'])
                    ->where('id', '!=', $id)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existingPricing) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Pricing already exists for this service and location type combination',
                        'data' => null
                    ], 400);
                }
            }

            $pricing->update($pricingData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service pricing updated successfully',
                'data' => $pricing->load(['service', 'createdBy', 'modifiedBy'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pricing = ServicePricing::whereNull('deleted_at')->findOrFail($id);

            // Soft delete
            $pricing->update([
                'deleted_at' => now(),
                'modified_by' => Auth::id()
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service pricing deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByService($serviceId)
    {
        try {
            $pricings = ServicePricing::with(['service'])
                ->where('service_id', $serviceId)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->orderBy('location_type')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Service pricing retrieved successfully',
                'data' => $pricings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByServiceAndCategory($serviceId, $categoryId)
    {
        try {
            // Since we don't have category_id in the pricing table,
            // we'll filter by location_type instead
            $pricings = ServicePricing::with(['service'])
                ->where('service_id', $serviceId)
                ->where('location_type', $categoryId) // Using categoryId as location_type
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Service pricing retrieved successfully',
                'data' => $pricings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function bulkUpdate($request)
    {
        DB::beginTransaction();
        try {
            $pricings = $request->pricings;
            $updated = [];

            foreach ($pricings as $pricingData) {
                // Add audit fields
                $pricingData['modified_by'] = Auth::id();
                $pricingData['updated_at'] = now();

                if (isset($pricingData['id'])) {
                    // Update existing pricing
                    $pricing = ServicePricing::whereNull('deleted_at')->findOrFail($pricingData['id']);
                    $pricing->update($pricingData);
                    $updated[] = $pricing->load(['service', 'createdBy', 'modifiedBy']);
                } else {
                    // Create new pricing
                    $pricingData['created_by'] = Auth::id();
                    $pricingData['created_at'] = now();
                    $pricing = ServicePricing::create($pricingData);
                    $updated[] = $pricing->load(['service', 'createdBy']);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service pricing updated successfully',
                'data' => $updated
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
