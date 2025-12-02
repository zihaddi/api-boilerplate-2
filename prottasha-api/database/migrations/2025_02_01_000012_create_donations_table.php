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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('users');

            // Donation details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('description')->nullable();
            $table->enum('donation_type', ['one_time', 'recurring_instance'])->default('one_time');

            // Optional project association
            $table->foreignId('project_id')->nullable()->constrained('projects');

            // Payment information
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card', 'digital_wallet', 'cryptocurrency']);
            $table->string('transaction_id', 100)->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_gateway', 50)->nullable();

            // Allocation tracking
            $table->decimal('allocated_amount', 10, 2)->default(0.00);
            $table->decimal('remaining_amount', 10, 2)->storedAs('amount - allocated_amount');

            // Timestamps
            $table->timestamp('donated_at')->useCurrent();
            $table->timestamp('payment_completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('donor_id');
            $table->index('project_id');
            $table->index('payment_status');
            $table->index('donated_at');
            $table->index('amount');

            // Constraints
            $table->check('amount > 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
