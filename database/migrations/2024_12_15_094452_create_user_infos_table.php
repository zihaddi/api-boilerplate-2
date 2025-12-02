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
        Schema::create('user_infos', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('photo')->nullable();
            $table->date('dob')->nullable()->comment('Date of birth');
            $table->tinyInteger('religion_id')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->string('occupation')->nullable();
            $table->integer('nationality_id')->nullable();
            $table->json('vulnerability_info')->nullable();
            $table->unsignedInteger('pre_country')->unsigned()->nullable();
            $table->text('pre_srteet_address')->nullable();
            $table->text('pre_city')->nullable();
            $table->text('pre_provience')->nullable();
            $table->string('pre_zip')->nullable();
            $table->boolean('same_as_present_address')->nullable();
            $table->unsignedInteger('per_country')->unsigned()->nullable();
            $table->text('per_srteet_address')->nullable();
            $table->text('per_city')->nullable();
            $table->text('per_provience')->nullable();
            $table->string('per_zip')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('user_infos', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_infos');
    }
};
