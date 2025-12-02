<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groomer_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('organization_id');
            $table->json('specializations')->nullable()->comment('JSON array of specializations');
            $table->integer('experience_years')->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->text('bio')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('groomer_profiles', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unique(['user_id', 'organization_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groomer_profiles');
    }
};
