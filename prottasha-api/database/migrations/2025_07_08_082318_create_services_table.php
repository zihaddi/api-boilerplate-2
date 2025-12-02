<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organization_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->integer('estimated_duration')->comment('Duration in minutes');
            $table->enum('category', ['grooming', 'bathing', 'nail_care', 'dental', 'specialty', 'other'])->default('grooming');
            $table->json('requires_pet_categories')->nullable()->comment('JSON array of applicable pet category IDs');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['organization_id', 'status']);
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
