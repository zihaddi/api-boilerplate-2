<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id');
            $table->string('name', 100);
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('subcategory_id');
            $table->unsignedInteger('breed_id');
            $table->date('birthday')->nullable();
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in kg');
            $table->enum('sex', ['male', 'female', 'unknown'])->default('unknown');
            $table->json('current_medications')->nullable()->comment('JSON array of current medications');
            $table->json('medication_allergies')->nullable()->comment('JSON array of medication allergies');
            $table->json('health_conditions')->nullable()->comment('JSON array of health conditions');
            $table->text('special_notes')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('pets', function (Blueprint $table) {
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('pet_categories')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('subcategory_id')->references('id')->on('pet_subcategories')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('breed_id')->references('id')->on('pet_breeds')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['owner_id', 'status']);
            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
