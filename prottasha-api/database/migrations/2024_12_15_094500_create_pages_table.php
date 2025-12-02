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
        Schema::create('pages', function (Blueprint $table) {
            $table->integerIncrements('id', 2);
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->foreign('created_by', 'pages_create')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by', 'pages_modify')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['title', 'status'], 'pages_index');
            $table->unique(['title'], 'pages_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
