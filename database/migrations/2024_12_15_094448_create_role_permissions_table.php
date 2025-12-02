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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->integerIncrements('id', 3);
            $table->tinyInteger('role_id')->nullable();
            $table->tinyInteger('view');
            $table->tinyInteger('add')->default('0');
            $table->tinyInteger('edit')->default('0');
            $table->tinyInteger('edit_other')->default('0');
            $table->tinyInteger('delete')->default('0');
            $table->tinyInteger('delete_other')->default('0');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign('created_by', 'role_per_create')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by', 'role_per_modify')->references('id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
            $table->index(['role_id', 'view', 'add', 'edit', 'edit_other', 'delete', 'delete_other'], 'role_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
