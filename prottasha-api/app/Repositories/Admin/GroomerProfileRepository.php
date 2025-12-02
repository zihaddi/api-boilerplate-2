<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\GroomerProfileRepositoryInterface;
use App\Models\GroomerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroomerProfileRepository implements GroomerProfileRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = GroomerProfile::with(['user', 'organization'])
                ->filter($request->all());

            if ($request->has('per_page')) {
                $groomerProfiles = $query->paginate($request->per_page);
            } else {
                $groomerProfiles = $query->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Groomer profiles retrieved successfully',
                'data' => $groomerProfiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving groomer profiles: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $groomerProfileData = $request->only([
                'user_id',
                'organization_id',
                'specializations',
                'experience_years',
                'hourly_rate',
                'bio',
                'status',
                'joined_at'
            ]);

            $groomerProfile = GroomerProfile::create($groomerProfileData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Groomer profile created successfully',
                'data' => $groomerProfile->load(['user', 'organization'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating groomer profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $groomerProfile = GroomerProfile::with(['user', 'organization', 'appointments.pet.owner', 'appointments.service'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Groomer profile retrieved successfully',
                'data' => $groomerProfile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving groomer profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $groomerProfile = GroomerProfile::findOrFail($id);

            $groomerProfileData = $request->only([
                'user_id',
                'organization_id',
                'specializations',
                'experience_years',
                'hourly_rate',
                'bio',
                'status',
                'joined_at'
            ]);

            $groomerProfile->update($groomerProfileData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Groomer profile updated successfully',
                'data' => $groomerProfile->load(['user', 'organization'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating groomer profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $groomerProfile = GroomerProfile::findOrFail($id);
            $groomerProfile->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Groomer profile deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting groomer profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByOrganization($organizationId)
    {
        try {
            $groomerProfiles = GroomerProfile::with(['user'])
                ->where('organization_id', $organizationId)
                ->where('status', true)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Organization groomer profiles retrieved successfully',
                'data' => $groomerProfiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving organization groomer profiles: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByUser($userId)
    {
        try {
            $groomerProfiles = GroomerProfile::with(['organization'])
                ->where('user_id', $userId)
                ->where('status', true)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'User groomer profiles retrieved successfully',
                'data' => $groomerProfiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving user groomer profiles: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
