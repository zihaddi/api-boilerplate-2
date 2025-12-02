<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_pricing', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->enum('location_type', ['in_house', 'at_organization']);
            $table->decimal('price', 10, 2);
            $table->decimal('additional_fees', 10, 2)->default(0.00)->comment('Travel fees, etc.');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('service_pricing', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unique(['service_id', 'location_type']);
            $table->index(['service_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_pricing');
    }
};
