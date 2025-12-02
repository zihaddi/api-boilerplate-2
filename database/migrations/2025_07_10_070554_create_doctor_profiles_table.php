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

        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('organization_id');
            $table->json('specializations')->nullable()->comment('JSON array of specializations');
            $table->integer('experience_years')->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->text('bio')->nullable();
            $table->string('medical_license_number')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
