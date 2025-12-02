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
        Schema::create('donation_deliveries', function (Blueprint $table) {
            $table->id();

            // Source allocation
            $table->foreignId('allocation_id')->constrained('donation_allocations');

            // Delivery details
            $table->foreignId('volunteer_id')->constrained('users');
            $table->foreignId('donation_taker_id')->constrained('users');

            $table->decimal('amount_delivered', 10, 2);
            $table->enum('delivery_method', ['cash', 'bank_transfer', 'mobile_money', 'goods', 'services']);

            // Delivery information
            $table->text('delivery_notes')->nullable();
            $table->text('delivery_location')->nullable();
            $table->boolean('recipient_confirmation')->default(false);
            $table->string('recipient_signature_url', 500)->nullable();

            // Verification
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->text('verification_notes')->nullable();

            $table->timestamp('delivered_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index('allocation_id');
            $table->index('volunteer_id');
            $table->index('donation_taker_id');
            $table->index('delivered_at');

            // Constraints
            $table->check('amount_delivered > 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_deliveries');
    }
};
