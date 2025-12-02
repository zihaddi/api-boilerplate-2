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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('full_name', 200)->storedAs("CONCAT(first_name, ' ', last_name)");
            $table->string('nid_or_passport', 50)->nullable()->unique();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('profile_image_url', 500)->nullable();
            $table->string('address_line_1', 200)->nullable();
            $table->string('address_line_2', 200)->nullable();
            $table->string('postal_code', 20)->nullable();

            // Location hierarchy (all required)
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('division_id')->constrained('divisions');
            $table->foreignId('district_id')->constrained('districts');
            $table->foreignId('thana_id')->constrained('thanas');
            $table->foreignId('upazila_id')->constrained('upazilas');
            $table->foreignId('union_id')->constrained('unions');

            // Additional fields for donation takers
            $table->foreignId('disability_id')->nullable()->constrained('disabilities');
            $table->text('disability_description')->nullable();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            // Profile completion and verification
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users'); // Reference to admin user

            $table->timestamps();

            // Indexes
            $table->index(['country_id', 'division_id', 'district_id', 'thana_id', 'upazila_id', 'union_id'], 'idx_location_full');
            $table->index('disability_id');
            $table->index('full_name');
            $table->index('nid_or_passport');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
