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
        Schema::create('unions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upazila_id')->constrained('upazilas')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('code', 10)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['upazila_id', 'name'], 'unique_union_per_upazila');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unions');
    }
};
