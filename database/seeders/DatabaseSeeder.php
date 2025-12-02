<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthClientsTableSeeder::class,
            RolesSeeder::class,
            AdminTableSeeder::class,
            BoilerplateMenuSeeder::class,
            RolePermissionsSeeder::class,
        ]);
    }
}
