<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\PetCategoryRepositoryInterface;
use App\Models\PetCategory;
use Illuminate\Support\Facades\DB;

class PetCategoryRepository implements PetCategoryRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = PetCategory::withCount('subcategories');

            if ($request->has('per_page')) {
                $categories = $query->paginate($request->per_page);
            } else {
                $categories = $query->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Pet categories retrieved successfully',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet categories: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $categoryData = $request->only([
                'name',
                'description',
                'is_active'
            ]);

            $category = PetCategory::create($categoryData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet category created successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating pet category: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = PetCategory::with(['subcategories.breeds'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Pet category retrieved successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet category: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $category = PetCategory::findOrFail($id);

            $categoryData = $request->only([
                'name',
                'description',
                'is_active'
            ]);

            $category->update($categoryData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet category updated successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating pet category: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $category = PetCategory::findOrFail($id);

            // Check if category has subcategories
            if ($category->subcategories()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete category with existing subcategories',
                    'data' => null
                ], 400);
            }

            $category->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pet category deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting pet category: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getActive()
    {
        try {
            $categories = PetCategory::where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Active pet categories retrieved successfully',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving active pet categories: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
