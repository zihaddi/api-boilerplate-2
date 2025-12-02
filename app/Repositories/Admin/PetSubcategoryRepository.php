<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\PetSubcategoryRepositoryInterface;
use App\Models\PetSubcategory;
use Illuminate\Support\Facades\DB;

class PetSubcategoryRepository implements PetSubcategoryRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = PetSubcategory::with(['category'])
                ->withCount('breeds')
                ->filter($request->all());

            if ($request->has('per_page')) {
                $subcategories = $query->paginate($request->per_page);
            } else {
                $subcategories = $query->get();
            }

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
            ], 500);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $subcategoryData = $request->only([
                'category_id',
                'name',
                'description',
                'is_active'
            ]);

            $subcategory = PetSubcategory::create($subcategoryData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet subcategory created successfully',
                'data' => $subcategory->load('category')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating pet subcategory: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $subcategory = PetSubcategory::with(['category', 'breeds'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Pet subcategory retrieved successfully',
                'data' => $subcategory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet subcategory: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $subcategory = PetSubcategory::findOrFail($id);

            $subcategoryData = $request->only([
                'category_id',
                'name',
                'description',
                'is_active'
            ]);

            $subcategory->update($subcategoryData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet subcategory updated successfully',
                'data' => $subcategory->load('category')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating pet subcategory: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $subcategory = PetSubcategory::findOrFail($id);

            // Check if subcategory has breeds
            if ($subcategory->breeds()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete subcategory with existing breeds',
                    'data' => null
                ], 400);
            }

            $subcategory->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet subcategory deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting pet subcategory: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByCategory($categoryId)
    {
        try {
            $subcategories = PetSubcategory::where('category_id', $categoryId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Category subcategories retrieved successfully',
                'data' => $subcategories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving category subcategories: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getActive()
    {
        try {
            $subcategories = PetSubcategory::with('category')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Active pet subcategories retrieved successfully',
                'data' => $subcategories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving active pet subcategories: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
