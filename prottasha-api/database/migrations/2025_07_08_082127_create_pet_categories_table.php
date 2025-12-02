<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('pet_categories', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['name', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_categories');
    }
};
