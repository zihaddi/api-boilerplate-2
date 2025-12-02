<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\ServiceRepositoryInterface;
use App\Models\Service;
use App\Models\ServicePricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = Service::with(['organization', 'servicePricing'])
                ->filter($request->all());

            if ($request->has('per_page')) {
                $services = $query->paginate($request->per_page);
            } else {
                $services = $query->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Services retrieved successfully',
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving services: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $serviceData = $request->only([
                'organization_id',
                'name',
                'description',
                'base_price',
                'estimated_duration',
                'category',
                'requires_pet_categories',
                'status'
            ]);

            $service = Service::create($serviceData);

            // Create service pricing if provided
            if ($request->has('pricing')) {
                foreach ($request->pricing as $pricing) {
                    ServicePricing::create([
                        'service_id' => $service->id,
                        'location_type' => $pricing['location_type'],
                        'price' => $pricing['price'],
                        'additional_fees' => $pricing['additional_fees'] ?? 0,
                        'status' => $pricing['status'] ?? true
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service created successfully',
                'data' => $service->load(['organization', 'servicePricing'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating service: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $service = Service::with(['organization', 'servicePricing', 'appointments.pet.owner', 'appointments.groomerProfile.user'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Service retrieved successfully',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving service: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $service = Service::findOrFail($id);

            $serviceData = $request->only([
                'organization_id',
                'name',
                'description',
                'base_price',
                'estimated_duration',
                'category',
                'requires_pet_categories',
                'status'
            ]);

            $service->update($serviceData);

            // Update service pricing if provided
            if ($request->has('pricing')) {
                // Delete existing pricing
                ServicePricing::where('service_id', $service->id)->delete();

                // Create new pricing
                foreach ($request->pricing as $pricing) {
                    ServicePricing::create([
                        'service_id' => $service->id,
                        'location_type' => $pricing['location_type'],
                        'price' => $pricing['price'],
                        'additional_fees' => $pricing['additional_fees'] ?? 0,
                        'status' => $pricing['status'] ?? true
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service updated successfully',
                'data' => $service->load(['organization', 'servicePricing'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating service: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $service = Service::findOrFail($id);

            // Check if service has appointments
            if ($service->appointments()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete service with existing appointments',
                    'data' => null
                ], 400);
            }

            $service->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting service: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByOrganization($organizationId)
    {
        try {
            $services = Service::with(['servicePricing'])
                ->where('organization_id', $organizationId)
                ->where('status', true)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Organization services retrieved successfully',
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving organization services: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getServicePricing($serviceId)
    {
        try {
            $servicePricing = ServicePricing::where('service_id', $serviceId)
                ->where('status', true)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Service pricing retrieved successfully',
                'data' => $servicePricing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving service pricing: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function updateServicePricing($serviceId, $request)
    {
        DB::beginTransaction();
        try {
            $service = Service::findOrFail($serviceId);

            // Delete existing pricing
            ServicePricing::where('service_id', $serviceId)->delete();

            // Create new pricing
            foreach ($request->pricing as $pricing) {
                ServicePricing::create([
                    'service_id' => $serviceId,
                    'location_type' => $pricing['location_type'],
                    'price' => $pricing['price'],
                    'additional_fees' => $pricing['additional_fees'] ?? 0,
                    'status' => $pricing['status'] ?? true
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Service pricing updated successfully',
                'data' => ServicePricing::where('service_id', $serviceId)->get()
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
