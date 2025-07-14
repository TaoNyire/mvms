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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Task Details
            $table->string('title');
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['draft', 'published', 'in_progress', 'completed', 'cancelled'])->default('draft');

            // Scheduling
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->json('recurring_pattern')->nullable(); // daily, weekly, monthly patterns
            $table->date('recurring_end_date')->nullable();

            // Location
            $table->enum('location_type', ['physical', 'remote', 'hybrid'])->default('physical');
            $table->text('location_address')->nullable();
            $table->string('location_coordinates')->nullable(); // lat,lng
            $table->text('location_instructions')->nullable();

            // Volunteer Requirements
            $table->integer('volunteers_needed')->default(1);
            $table->integer('volunteers_assigned')->default(0);
            $table->json('required_skills')->nullable();
            $table->text('special_requirements')->nullable();

            // Assignment Settings
            $table->enum('assignment_type', ['manual', 'automatic', 'first_come'])->default('manual');
            $table->boolean('allow_self_assignment')->default(false);
            $table->datetime('assignment_deadline')->nullable();

            // Completion Tracking
            $table->text('completion_notes')->nullable();
            $table->json('completion_checklist')->nullable();
            $table->boolean('requires_check_in')->default(false);
            $table->boolean('requires_check_out')->default(false);

            // Metadata
            $table->integer('estimated_hours')->nullable();
            $table->decimal('budget_allocated', 10, 2)->nullable();
            $table->json('equipment_needed')->nullable();
            $table->text('safety_requirements')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['opportunity_id', 'status']);
            $table->index(['start_datetime', 'end_datetime']);
            $table->index(['status', 'priority']);
            $table->index(['assignment_type', 'allow_self_assignment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
