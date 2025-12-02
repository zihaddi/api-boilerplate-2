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
        Schema::create('donation_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained('donations');

            // Allocation target (either project or direct allocation)
            $table->foreignId('project_id')->nullable()->constrained('projects');
            $table->foreignId('donation_taker_id')->nullable()->constrained('users');

            $table->decimal('allocated_amount', 10, 2);
            $table->enum('allocation_type', ['project', 'direct', 'monthly_support']);

            // Allocation details
            $table->text('purpose')->nullable();
            $table->foreignId('allocated_by')->constrained('users'); // Admin or system

            // Distribution tracking
            $table->decimal('distributed_amount', 10, 2)->default(0.00);
            $table->decimal('remaining_amount', 10, 2)->storedAs('allocated_amount - distributed_amount');

            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('active');

            $table->timestamp('allocated_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index('donation_id');
            $table->index('project_id');
            $table->index('donation_taker_id');
            $table->index('status');

            // Constraints
            $table->check('allocated_amount > 0');
        });

        // Add check constraint to ensure allocation is either to project or donation taker, not both
        DB::statement('ALTER TABLE donation_allocations ADD CONSTRAINT chk_allocation_target CHECK ((project_id IS NOT NULL AND donation_taker_id IS NULL) OR (project_id IS NULL AND donation_taker_id IS NOT NULL))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_allocations');
    }
};
