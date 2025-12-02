<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BoilerplateMenuSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('tree_entities')->truncate();
        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();
        $created_by = 1;

        $parentMenus = [
            [
                'pid' => 0,
                'node_name' => 'Dashboard',
                'route_name' => 'adminAuth',
                'route_location' => '/dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'status' => true,
                'serials' => 1,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'User Management',
                'route_name' => 'users',
                'route_location' => '/users',
                'icon' => 'fas fa-users',
                'status' => true,
                'serials' => 2,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'Role & Permissions',
                'route_name' => 'roles',
                'route_location' => '/roles',
                'icon' => 'fas fa-user-shield',
                'status' => true,
                'serials' => 3,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'Menu Management',
                'route_name' => 'tree-entity',
                'route_location' => '/tree-entity',
                'icon' => 'fas fa-bars',
                'status' => true,
                'serials' => 4,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'Payments',
                'route_name' => 'payments',
                'route_location' => '/payments',
                'icon' => 'fas fa-credit-card',
                'status' => true,
                'serials' => 5,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'Organizations',
                'route_name' => 'organizations',
                'route_location' => '/organizations',
                'icon' => 'fas fa-building',
                'status' => true,
                'serials' => 6,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'Services',
                'route_name' => 'services',
                'route_location' => '/services',
                'icon' => 'fas fa-concierge-bell',
                'status' => true,
                'serials' => 7,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'Appointments',
                'route_name' => 'appointments',
                'route_location' => '/appointments',
                'icon' => 'fas fa-calendar-check',
                'status' => true,
                'serials' => 8,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'Pet Management',
                'route_name' => 'pets',
                'route_location' => '/pets',
                'icon' => 'fas fa-paw',
                'status' => true,
                'serials' => 9,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pid' => 0,
                'node_name' => 'Settings',
                'route_name' => 'auth-client',
                'route_location' => '/settings',
                'icon' => 'fas fa-cog',
                'status' => true,
                'serials' => 10,
                'created_by' => $created_by,
                'modified_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('tree_entities')->insert($parentMenus);

        $userMgmtId = DB::table('tree_entities')->where('node_name', 'User Management')->value('id');
        $roleId = DB::table('tree_entities')->where('node_name', 'Role & Permissions')->value('id');
        $paymentsId = DB::table('tree_entities')->where('node_name', 'Payments')->value('id');
        $servicesId = DB::table('tree_entities')->where('node_name', 'Services')->value('id');
        $petsId = DB::table('tree_entities')->where('node_name', 'Pet Management')->value('id');
        $settingsId = DB::table('tree_entities')->where('node_name', 'Settings')->value('id');

        $childMenus = [
            ['pid' => $userMgmtId, 'node_name' => 'All Users', 'route_name' => 'users-list', 'route_location' => '/users/list', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $userMgmtId, 'node_name' => 'Add User', 'route_name' => 'users-add', 'route_location' => '/users/add', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $userMgmtId, 'node_name' => 'Deleted Users', 'route_name' => 'users-trash', 'route_location' => '/users/trash', 'icon' => null, 'status' => true, 'serials' => 3, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],

            ['pid' => $roleId, 'node_name' => 'All Roles', 'route_name' => 'roles-list', 'route_location' => '/roles/list', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $roleId, 'node_name' => 'Add Role', 'route_name' => 'roles-add', 'route_location' => '/roles/add', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $roleId, 'node_name' => 'Permissions', 'route_name' => 'role-permissions', 'route_location' => '/role-permissions', 'icon' => null, 'status' => true, 'serials' => 3, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],

            ['pid' => $paymentsId, 'node_name' => 'All Transactions', 'route_name' => 'payments-list', 'route_location' => '/payments/list', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $paymentsId, 'node_name' => 'Payment Settings', 'route_name' => 'payments-settings', 'route_location' => '/payments/settings', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $paymentsId, 'node_name' => 'Refunds', 'route_name' => 'payments-refunds', 'route_location' => '/payments/refunds', 'icon' => null, 'status' => true, 'serials' => 3, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],

            ['pid' => $servicesId, 'node_name' => 'All Services', 'route_name' => 'services-list', 'route_location' => '/services/list', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $servicesId, 'node_name' => 'Service Pricing', 'route_name' => 'service-pricing', 'route_location' => '/service-pricing', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],

            ['pid' => $petsId, 'node_name' => 'All Pets', 'route_name' => 'pets-list', 'route_location' => '/pets/list', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $petsId, 'node_name' => 'Pet Categories', 'route_name' => 'pet-categories', 'route_location' => '/pet-categories', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $petsId, 'node_name' => 'Pet Subcategories', 'route_name' => 'pet-subcategories', 'route_location' => '/pet-subcategories', 'icon' => null, 'status' => true, 'serials' => 3, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $petsId, 'node_name' => 'Pet Breeds', 'route_name' => 'pet-breeds', 'route_location' => '/pet-breeds', 'icon' => null, 'status' => true, 'serials' => 4, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],

            ['pid' => $settingsId, 'node_name' => 'General Settings', 'route_name' => 'settings-general', 'route_location' => '/settings/general', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
            ['pid' => $settingsId, 'node_name' => 'API Clients', 'route_name' => 'auth-client', 'route_location' => '/auth-client', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('tree_entities')->insert($childMenus);

        $this->command->info('Boilerplate admin menu seeded successfully!');
    }
}
