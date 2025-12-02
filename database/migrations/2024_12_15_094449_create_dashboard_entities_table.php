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
        Schema::create('dashboard_entities', function (Blueprint $table) {
            $table->integerIncrements('id', 3);
            $table->integer('pid')->unsigned()->nullable();
            $table->string('node_name');
            $table->string('slug')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('serials')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('modified_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('dashboard_entities', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')
                ->onDelete('set null')->onUpdate('cascade');
            $table->index(['pid', 'slug', 'node_name']);
            $table->unique(array('node_name'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_entities');
    }
};
