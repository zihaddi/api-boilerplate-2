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
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 200);
            $table->text('message');
            $table->enum('type', ['info', 'success', 'warning', 'error'])->default('info');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Notification metadata
            $table->string('related_entity_type', 50)->nullable();
            $table->unsignedBigInteger('related_entity_id')->nullable();

            // Status
            $table->timestamp('read_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'read', 'archived'])->default('pending');

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('priority');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
