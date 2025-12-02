<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\OrganizationRepositoryInterface;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = Organization::with(['groomerProfiles.user', 'services'])
                ->filter($request->all());

            if ($request->has('per_page')) {
                $organizations = $query->paginate($request->per_page);
            } else {
                $organizations = $query->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Organizations retrieved successfully',
                'data' => $organizations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving organizations: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $organizationData = $request->only([
                'name',
                'address',
                'phone',
                'email',
                'website',
                'is_default',
                'status'
            ]);

            $organization = Organization::create($organizationData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Organization created successfully',
                'data' => $organization
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating organization: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $organization = Organization::with(['groomerProfiles.user', 'services'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Organization retrieved successfully',
                'data' => $organization
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving organization: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $organization = Organization::findOrFail($id);

            $organizationData = $request->only([
                'name',
                'address',
                'phone',
                'email',
                'website',
                'is_default',
                'status'
            ]);

            $organization->update($organizationData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Organization updated successfully',
                'data' => $organization
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating organization: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $organization = Organization::findOrFail($id);
            $organization->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Organization deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting organization: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getActive()
    {
        try {
            $organizations = Organization::active()->get();

            return response()->json([
                'status' => true,
                'message' => 'Active organizations retrieved successfully',
                'data' => $organizations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving active organizations: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
