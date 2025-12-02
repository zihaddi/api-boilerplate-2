<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\DoctorProfileRepositoryInterface;
use App\Models\DoctorProfile;
use Illuminate\Support\Facades\DB;

class DoctorProfileRepository implements DoctorProfileRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = DoctorProfile::with(['user', 'organization'])
                ->filter($request->all());

            if ($request->has('per_page')) {
                $doctorProfiles = $query->paginate($request->per_page);
            } else {
                $doctorProfiles = $query->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Doctor profiles retrieved successfully',
                'data' => $doctorProfiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving doctor profiles: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $doctorProfileData = $request->only([
                'user_id',
                'organization_id',
                'specializations',
                'experience_years',
                'hourly_rate',
                'bio',
                'medical_license_number',
                'status',
                'joined_at'
            ]);

            $doctorProfile = DoctorProfile::create($doctorProfileData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Doctor profile created successfully',
                'data' => $doctorProfile->load(['user', 'organization'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating doctor profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $doctorProfile = DoctorProfile::with(['user', 'organization', 'appointments.pet.owner', 'appointments.service'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Doctor profile retrieved successfully',
                'data' => $doctorProfile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving doctor profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $doctorProfile = DoctorProfile::findOrFail($id);

            $doctorProfileData = $request->only([
                'user_id',
                'organization_id',
                'specializations',
                'experience_years',
                'hourly_rate',
                'bio',
                'medical_license_number',
                'status',
                'joined_at'
            ]);

            $doctorProfile->update($doctorProfileData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Doctor profile updated successfully',
                'data' => $doctorProfile->load(['user', 'organization'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating doctor profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $doctorProfile = DoctorProfile::findOrFail($id);
            $doctorProfile->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Doctor profile deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting doctor profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByOrganization($organizationId)
    {
        try {
            $doctorProfiles = DoctorProfile::with(['user', 'organization'])
                ->where('organization_id', $organizationId)
                ->where('status', true)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Doctor profiles retrieved successfully',
                'data' => $doctorProfiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving doctor profiles: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByUser($userId)
    {
        try {
            $doctorProfile = DoctorProfile::with(['user', 'organization'])
                ->where('user_id', $userId)
                ->first();

            if (!$doctorProfile) {
                return response()->json([
                    'status' => false,
                    'message' => 'Doctor profile not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Doctor profile retrieved successfully',
                'data' => $doctorProfile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving doctor profile: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
