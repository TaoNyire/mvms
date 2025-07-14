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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('volunteer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');

            // Assignment Details
            $table->enum('status', ['pending', 'accepted', 'declined', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->enum('assignment_method', ['manual', 'automatic', 'self_assigned'])->default('manual');
            $table->text('assignment_notes')->nullable();
            $table->text('volunteer_notes')->nullable();

            // Scheduling
            $table->datetime('scheduled_start');
            $table->datetime('scheduled_end');
            $table->datetime('actual_start')->nullable();
            $table->datetime('actual_end')->nullable();
            $table->integer('break_minutes')->default(0);

            // Response Tracking
            $table->datetime('assigned_at')->useCurrent();
            $table->datetime('responded_at')->nullable();
            $table->datetime('accepted_at')->nullable();
            $table->datetime('declined_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->text('decline_reason')->nullable();

            // Check-in/Check-out
            $table->datetime('checked_in_at')->nullable();
            $table->datetime('checked_out_at')->nullable();
            $table->string('check_in_location')->nullable(); // GPS coordinates
            $table->string('check_out_location')->nullable();
            $table->text('check_in_notes')->nullable();
            $table->text('check_out_notes')->nullable();

            // Performance & Feedback
            $table->integer('performance_rating')->nullable(); // 1-5 stars
            $table->text('performance_notes')->nullable();
            $table->text('volunteer_feedback')->nullable();
            $table->boolean('task_completed_successfully')->nullable();

            // Notifications
            $table->boolean('notification_sent')->default(false);
            $table->datetime('last_notification_sent')->nullable();
            $table->integer('notification_count')->default(0);
            $table->boolean('reminder_sent')->default(false);

            // Conflict Resolution
            $table->boolean('has_conflict')->default(false);
            $table->text('conflict_details')->nullable();
            $table->enum('conflict_status', ['none', 'detected', 'resolved', 'override'])->default('none');
            $table->datetime('conflict_resolved_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['task_id', 'status']);
            $table->index(['volunteer_id', 'status']);
            $table->index(['scheduled_start', 'scheduled_end']);
            $table->index(['status', 'assigned_at']);
            $table->unique(['task_id', 'volunteer_id']); // Prevent duplicate assignments
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
