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
        Schema::create('project_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('volunteer_id')->constrained('users');

            $table->enum('role', ['coordinator', 'field_worker', 'administrator', 'specialist'])->default('field_worker');
            $table->text('responsibilities')->nullable();

            // Assignment period
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('assignment_start_date')->nullable();
            $table->date('assignment_end_date')->nullable();

            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');

            // Performance tracking
            $table->integer('deliveries_count')->default(0);
            $table->decimal('total_amount_delivered', 10, 2)->default(0.00);

            $table->timestamps();

            // Indexes
            $table->index('project_id');
            $table->index('volunteer_id');
            $table->index('status');

            // Unique constraint
            $table->unique(['project_id', 'volunteer_id', 'status'], 'unique_volunteer_project_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_volunteers');
    }
};
