<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RolesSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $roles = [
            [
                'id' => 1,
                'role_name' => 'Super Admin',
                'status' => true,
                'created_by' => null,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'role_name' => 'Admin',
                'status' => true,
                'created_by' => null,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'role_name' => 'Manager',
                'status' => true,
                'created_by' => null,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'role_name' => 'Staff',
                'status' => true,
                'created_by' => null,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'role_name' => 'Customer',
                'status' => true,
                'created_by' => null,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('roles')->insert($roles);

        $this->command->info('Roles seeded successfully!');
    }
}
