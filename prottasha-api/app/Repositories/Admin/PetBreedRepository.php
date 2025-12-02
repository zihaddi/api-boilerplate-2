<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\PetBreedRepositoryInterface;
use App\Models\PetBreed;
use Illuminate\Support\Facades\DB;

class PetBreedRepository implements PetBreedRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = PetBreed::with(['subcategory.category'])
                ->filter($request->all());

            if ($request->has('per_page')) {
                $breeds = $query->paginate($request->per_page);
            } else {
                $breeds = $query->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Pet breeds retrieved successfully',
                'data' => $breeds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet breeds: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store($request)
    {

        DB::beginTransaction();
        try {
            $breedData = $request->only([
                'subcategory_id',
                'name',
                'description',
                'typical_weight_min',
                'typical_weight_max',
                'is_active'
            ]);



            $breed = PetBreed::create($breedData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet breed created successfully',
                'data' => $breed->load('subcategory.category')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating pet breed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $breed = PetBreed::with(['subcategory.category'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Pet breed retrieved successfully',
                'data' => $breed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet breed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $breed = PetBreed::findOrFail($id);

            $breedData = $request->only([
                'subcategory_id',
                'name',
                'description',
                'typical_weight_min',
                'typical_weight_max',
                'is_active'
            ]);

            $breed->update($breedData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet breed updated successfully',
                'data' => $breed->load('subcategory.category')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating pet breed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $breed = PetBreed::findOrFail($id);

            // Check if breed has pets
            if ($breed->pets()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete breed with existing pets',
                    'data' => null
                ], 400);
            }

            $breed->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet breed deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting pet breed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getBySubcategory($subcategoryId)
    {
        try {
            $breeds = PetBreed::where('subcategory_id', $subcategoryId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Subcategory breeds retrieved successfully',
                'data' => $breeds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving subcategory breeds: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getActive()
    {
        try {
            $breeds = PetBreed::with('subcategory.category')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Active pet breeds retrieved successfully',
                'data' => $breeds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving active pet breeds: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
