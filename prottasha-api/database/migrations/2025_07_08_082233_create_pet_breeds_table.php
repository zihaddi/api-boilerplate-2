<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_breeds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('subcategory_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('typical_weight_min', 5, 2)->nullable()->comment('Minimum typical weight in kg');
            $table->decimal('typical_weight_max', 5, 2)->nullable()->comment('Maximum typical weight in kg');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('pet_breeds', function (Blueprint $table) {
            $table->foreign('subcategory_id')->references('id')->on('pet_subcategories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unique(['subcategory_id', 'name']);
            $table->index(['subcategory_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_breeds');
    }
};
