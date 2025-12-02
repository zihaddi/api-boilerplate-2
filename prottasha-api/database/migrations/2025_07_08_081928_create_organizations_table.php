<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('address');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_default')->default(false)->comment('True for default org for independent groomers');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->index(['name', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
