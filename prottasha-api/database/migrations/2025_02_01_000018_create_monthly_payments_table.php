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
        Schema::create('monthly_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_program_id')->constrained('monthly_support_programs')->onDelete('cascade');

            // Payment details
            $table->decimal('amount', 8, 2);
            $table->char('payment_month', 7); // YYYY-MM format

            // Delivery
            $table->foreignId('delivered_by')->constrained('users');
            $table->enum('delivery_method', ['cash', 'bank_transfer', 'mobile_money']);

            // Status and verification
            $table->enum('status', ['scheduled', 'completed', 'failed', 'cancelled'])->default('scheduled');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('support_program_id');
            $table->index('payment_month');
            $table->index('status');

            // Unique constraint
            $table->unique(['support_program_id', 'payment_month'], 'unique_program_month');

            // Constraints
            $table->check('amount > 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_payments');
    }
};
