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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Schedule Type
            $table->enum('type', ['availability', 'unavailability', 'assignment', 'personal'])->default('availability');
            $table->string('title');
            $table->text('description')->nullable();

            // Timing
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->boolean('is_all_day')->default(false);
            $table->string('timezone')->default('Africa/Blantyre');

            // Recurrence
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->default(1); // every X days/weeks/months
            $table->json('recurrence_days')->nullable(); // [1,2,3,4,5] for weekdays
            $table->date('recurrence_end_date')->nullable();
            $table->integer('recurrence_count')->nullable(); // number of occurrences

            // Availability Specific
            $table->enum('availability_type', ['available', 'busy', 'tentative', 'out_of_office'])->nullable();
            $table->integer('max_hours_per_day')->nullable();
            $table->integer('max_hours_per_week')->nullable();
            $table->json('preferred_time_slots')->nullable(); // [{start: "09:00", end: "17:00"}]

            // Location Preferences
            $table->json('preferred_locations')->nullable(); // districts/regions
            $table->integer('max_travel_distance')->nullable(); // in kilometers
            $table->boolean('remote_work_available')->default(false);

            // Assignment Reference
            $table->foreignId('assignment_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('cascade');

            // Metadata
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('is_flexible')->default(false);
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['start_datetime', 'end_datetime']);
            $table->index(['type', 'availability_type']);
            $table->index(['is_recurring', 'recurrence_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
