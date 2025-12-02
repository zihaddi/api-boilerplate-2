<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds for admin menu items.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing menu data to prevent duplicates
        DB::table('tree_entities')->truncate();

        $now = Carbon::now();
$created_by = 1; // Or use Auth::id() in an authenticated context

// Parent Menus
$parentMenus = [
    [
        'pid' => 0,
        'node_name' => 'About',
        'route_name' => 'about',
        'route_location' => '/about',
        'icon' => 'fas fa-info-circle',
        'status' => true,
        'serials' => 1,
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
        'serials' => 2,
        'created_by' => $created_by,
        'modified_by' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'pid' => 0,
        'node_name' => 'Get Involve',
        'route_name' => 'career',
        'route_location' => '/career',
        'icon' => 'fas fa-handshake',
        'status' => true,
        'serials' => 3,
        'created_by' => $created_by,
        'modified_by' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'pid' => 0,
        'node_name' => 'At a Glance',
        'route_name' => 'at-a-glance',
        'route_location' => '/at-a-glace',
        'icon' => 'fas fa-eye',
        'status' => true,
        'serials' => 4,
        'created_by' => $created_by,
        'modified_by' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'pid' => 0,
        'node_name' => 'Resources',
        'route_name' => 'resources',
        'route_location' => '/resources',
        'icon' => 'fas fa-archive',
        'status' => true,
        'serials' => 5,
        'created_by' => $created_by,
        'modified_by' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'pid' => 0,
        'node_name' => 'Blogs',
        'route_name' => 'blogs',
        'route_location' => '/blogs',
        'icon' => 'fas fa-blog',
        'status' => true,
        'serials' => 6,
        'created_by' => $created_by,
        'modified_by' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'pid' => 0,
        'node_name' => 'FAQ',
        'route_name' => 'faq',
        'route_location' => '/faq',
        'icon' => 'fas fa-question-circle',
        'status' => true,
        'serials' => 7,
        'created_by' => $created_by,
        'modified_by' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ],
];

DB::table('tree_entities')->insert($parentMenus);

// Get inserted parent menu IDs
$aboutId = DB::table('tree_entities')->where('node_name', 'About')->value('id');
$servicesId = DB::table('tree_entities')->where('node_name', 'Services')->value('id');
$careerId = DB::table('tree_entities')->where('node_name', 'Get Involve')->value('id');
$resourcesId = DB::table('tree_entities')->where('node_name', 'Resources')->value('id');

// Child Menus
$childMenus = [
    // About children
    ['pid' => $aboutId, 'node_name' => 'The Context', 'route_name' => 'about-context', 'route_location' => '/about/context', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $aboutId, 'node_name' => 'About CCS', 'route_name' => 'about-ccs', 'route_location' => '/about/about', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $aboutId, 'node_name' => 'Mission', 'route_name' => 'mission', 'route_location' => '/about/mission', 'icon' => null, 'status' => true, 'serials' => 3, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $aboutId, 'node_name' => 'Advisor Body', 'route_name' => 'advisor-body', 'route_location' => '/about/advisor-body', 'icon' => null, 'status' => true, 'serials' => 4, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $aboutId, 'node_name' => 'Executive Body', 'route_name' => 'executive-body', 'route_location' => '/about/executive-body', 'icon' => null, 'status' => true, 'serials' => 5, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $aboutId, 'node_name' => 'Member Of CCS', 'route_name' => 'ccs-member', 'route_location' => '/about/ccs-member', 'icon' => null, 'status' => true, 'serials' => 6, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $aboutId, 'node_name' => 'Administration', 'route_name' => 'administration', 'route_location' => '/about/administration', 'icon' => null, 'status' => true, 'serials' => 7, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $aboutId, 'node_name' => 'Branch', 'route_name' => 'branches', 'route_location' => '/about/branches', 'icon' => null, 'status' => true, 'serials' => 8, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $aboutId, 'node_name' => 'Information', 'route_name' => 'information', 'route_location' => '/about/information', 'icon' => null, 'status' => true, 'serials' => 9, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],

    // Services children
    ['pid' => $servicesId, 'node_name' => 'Consumer Development', 'route_name' => 'consumer-development', 'route_location' => '/services/consumer-development', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $servicesId, 'node_name' => 'Youth Engagement', 'route_name' => 'youth-engage', 'route_location' => '/services/youth-engage', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $servicesId, 'node_name' => 'CCS Digital Lab', 'route_name' => 'digital-lab', 'route_location' => '/services/digital-lab', 'icon' => null, 'status' => true, 'serials' => 3, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $servicesId, 'node_name' => 'CCS Housing Project', 'route_name' => 'housing-project', 'route_location' => '/services/housing-project', 'icon' => null, 'status' => true, 'serials' => 4, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $servicesId, 'node_name' => 'CCS Blood Bank', 'route_name' => 'blood-bank', 'route_location' => '/services/blood-bank', 'icon' => null, 'status' => true, 'serials' => 5, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $servicesId, 'node_name' => 'CCS Volunteers', 'route_name' => 'volunteers', 'route_location' => '/services/volunteers', 'icon' => null, 'status' => true, 'serials' => 6, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $servicesId, 'node_name' => 'Journalists Awards', 'route_name' => 'jounalist-awards', 'route_location' => '/services/jounalist-awards', 'icon' => null, 'status' => true, 'serials' => 7, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],

    // Career children
    ['pid' => $careerId, 'node_name' => 'Vacancy', 'route_name' => 'vacancy', 'route_location' => '/career/vacancy', 'icon' => null, 'status' => true, 'serials' => 1, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $careerId, 'node_name' => 'Internship', 'route_name' => 'internship', 'route_location' => '/career/internship', 'icon' => null, 'status' => true, 'serials' => 2, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $careerId, 'node_name' => 'Fellowship', 'route_name' => 'fellowship', 'route_location' => '/career/fellowship', 'icon' => null, 'status' => true, 'serials' => 3, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $careerId, 'node_name' => 'Membership', 'route_name' => 'membership', 'route_location' => '/career/membership', 'icon' => null, 'status' => true, 'serials' => 4, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $careerId, 'node_name' => 'Partnership', 'route_name' => 'partnership', 'route_location' => '/career/partnership', 'icon' => null, 'status' => true, 'serials' => 5, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $careerId, 'node_name' => 'Sposorship', 'route_name' => 'sposorship', 'route_location' => '/career/sposorship', 'icon' => null, 'status' => true, 'serials' => 6, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $careerId, 'node_name' => 'Advisorship', 'route_name' => 'advisorship', 'route_location' => '/career/advisorship', 'icon' => null, 'status' => true, 'serials' => 7, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
    ['pid' => $careerId, 'node_name' => 'Donorship', 'route_name' => 'donorship', 'route_location' => '/career/donorship', 'icon' => null, 'status' => true, 'serials' => 8, 'created_by' => $created_by, 'modified_by' => null, 'created_at' => $now, 'updated_at' => $now],
];

    DB::table('tree_entities')->insert($childMenus);


        $this->command->info('Admin menu seeded successfully!');
    }
}
