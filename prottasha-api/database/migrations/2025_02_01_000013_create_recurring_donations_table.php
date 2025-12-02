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
        Schema::create('recurring_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('users');

            // Recurring donation setup
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('description')->nullable();
            $table->enum('frequency', ['weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');

            // Optional project association
            $table->foreignId('project_id')->nullable()->constrained('projects');

            // Schedule
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_payment_date');

            // Status and tracking
            $table->enum('status', ['active', 'paused', 'cancelled', 'completed'])->default('active');
            $table->integer('total_payments_made')->default(0);
            $table->decimal('total_amount_donated', 12, 2)->default(0.00);

            // Payment method
            $table->enum('payment_method', ['bank_transfer', 'credit_card', 'digital_wallet']);
            $table->string('payment_token', 255)->nullable(); // For stored payment methods

            $table->timestamps();

            // Indexes
            $table->index('donor_id');
            $table->index('status');
            $table->index('next_payment_date');
            $table->index('frequency');

            // Constraints
            $table->check('amount > 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_donations');
    }
};
