<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Disability;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;

class GeographicDataController extends Controller
{
    /**
     * Get all countries
     */
    public function getCountries(Request $request)
    {
        try {
            $countries = Country::active()->orderBy('name')->get();
            return response()->json([
                'success' => true,
                'data' => $countries,
                'message' => 'Countries retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get divisions by country
     */
    public function getDivisions(Request $request, $countryId = null)
    {
        try {
            $query = Division::with('country')->active()->orderBy('name');

            if ($countryId) {
                $query->where('country_id', $countryId);
            }

            $divisions = $query->get();

            return response()->json([
                'success' => true,
                'data' => $divisions,
                'message' => 'Divisions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get districts by division
     */
    public function getDistricts(Request $request, $divisionId = null)
    {
        try {
            $query = District::with(['division', 'country'])->active()->orderBy('name');

            if ($divisionId) {
                $query->where('division_id', $divisionId);
            }

            $districts = $query->get();

            return response()->json([
                'success' => true,
                'data' => $districts,
                'message' => 'Districts retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get thanas by district
     */
    public function getThanas(Request $request, $districtId = null)
    {
        try {
            $query = Thana::with(['district', 'division', 'country'])->active()->orderBy('name');

            if ($districtId) {
                $query->where('district_id', $districtId);
            }

            $thanas = $query->get();

            return response()->json([
                'success' => true,
                'data' => $thanas,
                'message' => 'Thanas retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upazilas by district
     */
    public function getUpazilas(Request $request, $districtId = null)
    {
        try {
            $query = Upazila::with(['district', 'division', 'country'])->active()->orderBy('name');

            if ($districtId) {
                $query->where('district_id', $districtId);
            }

            $upazilas = $query->get();

            return response()->json([
                'success' => true,
                'data' => $upazilas,
                'message' => 'Upazilas retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unions by upazila
     */
    public function getUnions(Request $request, $upazilaId = null)
    {
        try {
            $query = Union::with(['upazila', 'district', 'division', 'country'])->active()->orderBy('name');

            if ($upazilaId) {
                $query->where('upazila_id', $upazilaId);
            }

            $unions = $query->get();

            return response()->json([
                'success' => true,
                'data' => $unions,
                'message' => 'Unions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all disabilities
     */
    public function getDisabilities(Request $request)
    {
        try {
            $disabilities = Disability::active()->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $disabilities,
                'message' => 'Disabilities retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all project categories
     */
    public function getProjectCategories(Request $request)
    {
        try {
            $categories = ProjectCategory::active()->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Project categories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get geographic hierarchy for a specific location
     */
    public function getLocationHierarchy(Request $request, $level, $id)
    {
        try {
            $data = [];

            switch ($level) {
                case 'country':
                    $country = Country::with(['divisions.districts.thanas', 'divisions.districts.upazilas.unions'])->find($id);
                    $data = $country;
                    break;

                case 'division':
                    $division = Division::with(['country', 'districts.thanas', 'districts.upazilas.unions'])->find($id);
                    $data = $division;
                    break;

                case 'district':
                    $district = District::with(['country', 'division', 'thanas', 'upazilas.unions'])->find($id);
                    $data = $district;
                    break;

                case 'thana':
                    $thana = Thana::with(['country', 'division', 'district'])->find($id);
                    $data = $thana;
                    break;

                case 'upazila':
                    $upazila = Upazila::with(['country', 'division', 'district', 'unions'])->find($id);
                    $data = $upazila;
                    break;

                case 'union':
                    $union = Union::with(['country', 'division', 'district', 'upazila'])->find($id);
                    $data = $union;
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid location level'
                    ], 400);
            }

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Location hierarchy retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
