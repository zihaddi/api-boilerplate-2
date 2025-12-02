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
        Schema::create('dashboard_permissions', function (Blueprint $table) {
            $table->integerIncrements('id', 3);
            $table->tinyInteger('role_id')->nullable();
            $table->tinyInteger('view');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('dashboard_permissions', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
            $table->index(['role_id', 'view']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_permissions');
    }
};
