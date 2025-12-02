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
        Schema::create('monthly_support_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_taker_id')->constrained('users');

            // Support details
            $table->decimal('monthly_amount', 8, 2);
            $table->enum('support_type', ['disability_support', 'general_assistance', 'emergency_aid', 'educational_support']);

            // Program duration
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // Assignment
            $table->foreignId('assigned_volunteer_id')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');

            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');

            // Tracking
            $table->integer('total_payments_made')->default(0);
            $table->decimal('total_amount_paid', 10, 2)->default(0.00);
            $table->date('next_payment_due')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('donation_taker_id');
            $table->index('assigned_volunteer_id');
            $table->index('status');
            $table->index('next_payment_due');

            // Constraints
            $table->check('monthly_amount > 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_support_programs');
    }
};
