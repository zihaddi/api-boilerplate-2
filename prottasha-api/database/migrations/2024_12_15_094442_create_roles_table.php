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
        Schema::create('roles', function (Blueprint $table) {
            $table->integerIncrements('id', 3);
            $table->string('role_name');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
            $table->unique('role_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
