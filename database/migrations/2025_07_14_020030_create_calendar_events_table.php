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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Event Details
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['task', 'assignment', 'meeting', 'training', 'personal', 'holiday'])->default('task');
            $table->enum('status', ['confirmed', 'tentative', 'cancelled'])->default('confirmed');

            // Timing
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->boolean('is_all_day')->default(false);
            $table->string('timezone')->default('Africa/Blantyre');

            // Location
            $table->text('location')->nullable();
            $table->string('location_coordinates')->nullable();
            $table->enum('location_type', ['physical', 'remote', 'hybrid'])->nullable();

            // References
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('assignment_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('opportunity_id')->nullable()->constrained()->onDelete('cascade');

            // Recurrence
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule')->nullable(); // RRULE format
            $table->foreignId('parent_event_id')->nullable()->constrained('calendar_events')->onDelete('cascade');

            // Reminders & Notifications
            $table->json('reminder_times')->nullable(); // [15, 60, 1440] minutes before
            $table->boolean('email_reminder')->default(true);
            $table->boolean('sms_reminder')->default(false);
            $table->datetime('last_reminder_sent')->nullable();

            // Attendees & Collaboration
            $table->json('attendees')->nullable(); // [{user_id: 1, status: 'accepted'}]
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_private')->default(false);

            // Visual & Categorization
            $table->string('color', 7)->default('#007bff'); // hex color
            $table->json('tags')->nullable();
            $table->integer('priority')->default(3); // 1-5 scale

            // Metadata
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('external_id')->nullable(); // for syncing with external calendars

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['start_datetime', 'end_datetime']);
            $table->index(['type', 'status']);
            $table->index(['task_id', 'assignment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
