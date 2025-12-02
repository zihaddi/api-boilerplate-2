<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RolePermissionsSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('role_permissions')->truncate();
        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $treeEntities = DB::table('tree_entities')->get();

        $permissions = [];

        foreach ($treeEntities as $entity) {
            $permissions[] = [
                'role_id' => 1,
                'view' => $entity->id,
                'add' => $entity->id,
                'edit' => $entity->id,
                'edit_other' => $entity->id,
                'delete' => $entity->id,
                'delete_other' => $entity->id,
                'created_by' => 1,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $adminExcludedRoutes = ['auth-client'];
        foreach ($treeEntities as $entity) {
            if (!in_array($entity->route_name, $adminExcludedRoutes)) {
                $permissions[] = [
                    'role_id' => 2,
                    'view' => $entity->id,
                    'add' => $entity->id,
                    'edit' => $entity->id,
                    'edit_other' => 0,
                    'delete' => 0,
                    'delete_other' => 0,
                    'created_by' => 1,
                    'modified_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $managerRoutes = ['adminAuth', 'appointments', 'services', 'pets'];
        foreach ($treeEntities as $entity) {
            if (in_array($entity->route_name, $managerRoutes)) {
                $permissions[] = [
                    'role_id' => 3,
                    'view' => $entity->id,
                    'add' => $entity->id,
                    'edit' => $entity->id,
                    'edit_other' => 0,
                    'delete' => 0,
                    'delete_other' => 0,
                    'created_by' => 1,
                    'modified_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $staffRoutes = ['adminAuth', 'appointments', 'pets'];
        foreach ($treeEntities as $entity) {
            if (in_array($entity->route_name, $staffRoutes)) {
                $permissions[] = [
                    'role_id' => 4,
                    'view' => $entity->id,
                    'add' => $entity->id,
                    'edit' => 0,
                    'edit_other' => 0,
                    'delete' => 0,
                    'delete_other' => 0,
                    'created_by' => 1,
                    'modified_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $customerRoutes = ['adminAuth'];
        foreach ($treeEntities as $entity) {
            if (in_array($entity->route_name, $customerRoutes)) {
                $permissions[] = [
                    'role_id' => 5,
                    'view' => $entity->id,
                    'add' => 0,
                    'edit' => 0,
                    'edit_other' => 0,
                    'delete' => 0,
                    'delete_other' => 0,
                    'created_by' => 1,
                    'modified_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_permissions')->insert($permissions);

        $this->command->info('Role permissions seeded successfully!');
    }
}
