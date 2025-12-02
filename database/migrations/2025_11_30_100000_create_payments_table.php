<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('transaction_id')->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('gateway')->comment('stripe, paypal, sslcommerz, manual');
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_customer_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('gateway_fee', 10, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->nullable();
            $table->string('status')->default('pending')->comment('pending, processing, completed, failed, refunded, cancelled');
            $table->string('payment_method')->nullable()->comment('card, bank, wallet, etc.');
            $table->string('payment_type')->default('one_time')->comment('one_time, subscription, recurring');
            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('metadata')->nullable();
            $table->text('description')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('refund_reason')->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['gateway', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
