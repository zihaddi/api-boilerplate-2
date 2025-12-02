<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PetCategory;
use App\Models\PetSubcategory;
use App\Models\PetBreed;
use App\Models\Organization;
use App\Models\User;

class PetCareSeeder extends Seeder
{
    public function run(): void
    {
        // Create Pet Categories
        $categories = [
            ['name' => 'Dog', 'description' => 'Domestic dogs of all sizes'],
            ['name' => 'Cat', 'description' => 'Domestic cats'],
            ['name' => 'Bird', 'description' => 'Pet birds including parrots, canaries, etc.'],
            ['name' => 'Rabbit', 'description' => 'Domestic rabbits'],
            ['name' => 'Other', 'description' => 'Other small pets'],
        ];

        foreach ($categories as $category) {
            PetCategory::create($category);
        }

        // Create Pet Subcategories
        $subcategories = [
            ['category_id' => 1, 'name' => 'Small Dog', 'description' => 'Dogs under 25 lbs'],
            ['category_id' => 1, 'name' => 'Medium Dog', 'description' => 'Dogs between 25-60 lbs'],
            ['category_id' => 1, 'name' => 'Large Dog', 'description' => 'Dogs over 60 lbs'],
            ['category_id' => 2, 'name' => 'Short Hair Cat', 'description' => 'Cats with short fur'],
            ['category_id' => 2, 'name' => 'Long Hair Cat', 'description' => 'Cats with long fur'],
            ['category_id' => 3, 'name' => 'Small Bird', 'description' => 'Birds like canaries, finches'],
            ['category_id' => 3, 'name' => 'Large Bird', 'description' => 'Birds like parrots, macaws'],
        ];

        foreach ($subcategories as $subcategory) {
            PetSubcategory::create($subcategory);
        }

        // Create Pet Breeds
        $breeds = [
            ['subcategory_id' => 1, 'name' => 'Chihuahua', 'description' => 'Very small toy breed', 'typical_weight_min' => 1.5, 'typical_weight_max' => 3.0],
            ['subcategory_id' => 1, 'name' => 'Yorkshire Terrier', 'description' => 'Small terrier breed', 'typical_weight_min' => 2.0, 'typical_weight_max' => 3.5],
            ['subcategory_id' => 2, 'name' => 'Golden Retriever', 'description' => 'Medium to large retriever', 'typical_weight_min' => 25.0, 'typical_weight_max' => 35.0],
            ['subcategory_id' => 2, 'name' => 'Labrador', 'description' => 'Popular family dog', 'typical_weight_min' => 25.0, 'typical_weight_max' => 36.0],
            ['subcategory_id' => 3, 'name' => 'German Shepherd', 'description' => 'Large working breed', 'typical_weight_min' => 22.0, 'typical_weight_max' => 40.0],
            ['subcategory_id' => 4, 'name' => 'British Shorthair', 'description' => 'Stocky short-haired cat', 'typical_weight_min' => 3.5, 'typical_weight_max' => 7.0],
            ['subcategory_id' => 5, 'name' => 'Persian', 'description' => 'Long-haired cat breed', 'typical_weight_min' => 3.0, 'typical_weight_max' => 5.5],
        ];

        foreach ($breeds as $breed) {
            PetBreed::create($breed);
        }

        // Create Default Organization for Independent Groomers
        Organization::create([
            'name' => 'Independent Groomers',
            'address' => 'Various Locations',
            'phone' => null,
            'email' => null,
            'is_default' => true,
            'status' => true,
        ]);

        // Create Sample Users
        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('password'),
            'full_name' => 'John Doe',
            'phone' => '+1234567890',
            'address' => '123 Main St, City, State',
            'pet_user_type' => 'pet_owner',
            'status' => true,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => bcrypt('password'),
            'full_name' => 'Jane Smith',
            'phone' => '+1234567891',
            'address' => '456 Oak Ave, City, State',
            'pet_user_type' => 'groomer',
            'status' => true,
        ]);
    }
}
