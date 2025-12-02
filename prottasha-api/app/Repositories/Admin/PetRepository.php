<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\PetRepositoryInterface;
use App\Models\Pet;
use App\Models\PetCategory;
use App\Models\PetSubcategory;
use App\Models\PetBreed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PetRepository implements PetRepositoryInterface
{
    public function index($request)
    {
        try {
        $query = Pet::with(['owner', 'category', 'subcategory', 'breed'])
            ->filter($request->all());

        if ($request->has('per_page')) {
            $pets = $query->paginate($request->per_page);
        } else {
            $pets = $query->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Pets retrieved successfully',
            'data' => $pets
        ], 200); // 200 is the HTTP status code for success
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error retrieving pets: ' . $e->getMessage(),
            'data' => null
        ], 500); // 500 for server error
    }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $petData = $request->only([
                'owner_id',
                'name',
                'category_id',
                'subcategory_id',
                'breed_id',
                'birthday',
                'weight',
                'sex',
                'current_medications',
                'medication_allergies',
                'health_conditions',
                'special_notes',
                'photo',
                'status'
            ]);

            $pet = Pet::create($petData);

            DB::commit();
            return response()->json( [
                'status' => true,
                'message' => 'Pet created successfully',
                'data' => $pet->load(['owner', 'category', 'subcategory', 'breed'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating pet: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function show($id)
    {
        try {
            $pet = Pet::with(['owner', 'category', 'subcategory', 'breed', 'appointments.groomerProfile.user', 'appointments.service'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Pet retrieved successfully',
                'data' => $pet
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $pet = Pet::findOrFail($id);

            $petData = $request->only([
                'owner_id',
                'name',
                'category_id',
                'subcategory_id',
                'breed_id',
                'birthday',
                'weight',
                'sex',
                'current_medications',
                'medication_allergies',
                'health_conditions',
                'special_notes',
                'photo',
                'status'
            ]);

            $pet->update($petData);

            DB::commit();
            return response()->json( [
                'status' => true,
                'message' => 'Pet updated successfully',
                'data' => $pet->load(['owner', 'category', 'subcategory', 'breed'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json( [
                'status' => false,
                'message' => 'Error updating pet: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pet = Pet::findOrFail($id);
            $pet->delete();

            DB::commit();
            return response()->json( [
                'status' => true,
                'message' => 'Pet deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json( [
                'status' => false,
                'message' => 'Error deleting pet: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function getByOwner($ownerId)
    {
        try {
            $pets = Pet::with(['category', 'subcategory', 'breed'])
                ->where('owner_id', $ownerId)
                ->where('status', true)
                ->get();

            return response()->json( [
                'status' => true,
                'message' => 'Owner pets retrieved successfully',
                'data' => $pets
            ]);
        } catch (\Exception $e) {
            return response()->json( [
                'status' => false,
                'message' => 'Error retrieving owner pets: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function getPetCategories()
    {
        try {
            $categories = PetCategory::active()->get();
            return response()->json( [
                'status' => true,
                'message' => 'Pet categories retrieved successfully',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet categories: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function getPetSubcategories($categoryId = null)
    {
        try {
            $query = PetSubcategory::with('category')->active();

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $subcategories = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'Pet subcategories retrieved successfully',
                'data' => $subcategories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet subcategories: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function getPetBreeds($subcategoryId = null)
    {
        try {
            $query = PetBreed::with('subcategory.category')->active();

            if ($subcategoryId) {
                $query->where('subcategory_id', $subcategoryId);
            }

            $breeds = $query->get();

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
            ]);
        }
    }
}
