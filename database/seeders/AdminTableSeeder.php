<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminTableSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = [
            'uid' => Str::uuid(),
            'mobile' => '01771882876',
            'ccode' => '+1',
            'email' => 'admin@boilerplate.com',
            'password' => bcrypt('password123'),
            'auth_code' => null,
            'is_verify' => 1,
            'status' => 1,
            'user_type' => 1,
            'mobile_verified_at' => Carbon::now(),
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $userId = DB::table('users')->insertGetId($adminUser);

        $adminUserInfo = [
            'user_id' => $userId,
            'first_name' => 'Super',
            'middle_name' => null,
            'last_name' => 'Admin',
            'photo' => null,
            'dob' => Carbon::now()->subYears(30)->format('Y-m-d'),
            'religion_id' => null,
            'gender' => 1,
            'occupation' => 'System Administrator',
            'nationality_id' => null,
            'vulnerability_info' => json_encode(['info' => 'None']),
            'pre_country' => 1,
            'pre_srteet_address' => '123 Admin Street',
            'pre_city' => 'Admin City',
            'pre_provience' => 'Admin Province',
            'pre_zip' => '12345',
            'same_as_present_address' => 1,
            'per_country' => 1,
            'per_srteet_address' => '123 Admin Street',
            'per_city' => 'Admin City',
            'per_provience' => 'Admin Province',
            'per_zip' => '12345',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        DB::table('user_infos')->insert($adminUserInfo);

        $this->command->info('Super Admin user created successfully!');
        $this->command->info('Email: admin@boilerplate.com');
        $this->command->info('Password: password123');
    }
}
