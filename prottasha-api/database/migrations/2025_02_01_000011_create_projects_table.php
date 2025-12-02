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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description');
            $table->foreignId('category_id')->nullable()->constrained('project_categories');

            // Project financial information
            $table->decimal('target_amount', 12, 2)->default(0.00);
            $table->decimal('raised_amount', 12, 2)->default(0.00);
            $table->decimal('allocated_amount', 12, 2)->default(0.00);
            $table->decimal('distributed_amount', 12, 2)->default(0.00);

            // Project timeline
            $table->date('start_date');
            $table->date('end_date');

            // Project location scope
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->foreignId('thana_id')->nullable()->constrained('thanas');
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas');
            $table->foreignId('union_id')->nullable()->constrained('unions');

            // Project management
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('managed_by')->nullable()->constrained('users'); // Current project manager
            $table->enum('status', ['planning', 'active', 'paused', 'completed', 'cancelled'])->default('planning');

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index(['start_date', 'end_date']);
            $table->index(['country_id', 'division_id', 'district_id']);
            $table->index(['target_amount', 'raised_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
