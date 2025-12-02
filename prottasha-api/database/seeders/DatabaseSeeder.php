<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(AuthClientsTableSeeder::class);
        $this->call(AdminTableSeeder::class);
        $this->call(AdminMenuSeeder::class);

        // Event Management Seeders
        $this->call([
            // EventCategorySeeder::class,
            // YearSeeder::class,
            // EventSeeder::class,
        ]);
    }
}
