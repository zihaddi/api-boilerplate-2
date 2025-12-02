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
        Schema::create('page_layout_infos', function (Blueprint $table) {
            $table->integerIncrements('id', 2);
            $table->unsignedInteger('page_id')->nullable();
            $table->json('data')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('page_layout_infos', function (Blueprint $table) {
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['page_id']);
            $table->unique(['page_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_layout_infos');
    }
};
