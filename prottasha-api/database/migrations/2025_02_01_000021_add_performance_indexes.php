<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add additional composite indexes for performance
        Schema::table('users', function (Blueprint $table) {
            $table->index(['user_type', 'status'], 'idx_users_type_status');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->index(['donor_id', 'donated_at'], 'idx_donations_donor_date');
        });

        Schema::table('donation_deliveries', function (Blueprint $table) {
            $table->index(['volunteer_id', 'delivered_at'], 'idx_deliveries_volunteer_date');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->index(['disability_id', 'country_id', 'division_id', 'district_id'], 'idx_profiles_location_disability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_type_status');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex('idx_donations_donor_date');
        });

        Schema::table('donation_deliveries', function (Blueprint $table) {
            $table->dropIndex('idx_deliveries_volunteer_date');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropIndex('idx_profiles_location_disability');
        });
    }
};
