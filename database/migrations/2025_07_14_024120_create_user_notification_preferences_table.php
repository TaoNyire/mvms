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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // General Notification Settings
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('push_notifications')->default(true);

            // Notification Types - In-App
            $table->boolean('in_app_task_assigned')->default(true);
            $table->boolean('in_app_task_updated')->default(true);
            $table->boolean('in_app_application_status')->default(true);
            $table->boolean('in_app_messages')->default(true);
            $table->boolean('in_app_announcements')->default(true);
            $table->boolean('in_app_reminders')->default(true);
            $table->boolean('in_app_schedule_changes')->default(true);

            // Notification Types - Email
            $table->boolean('email_task_assigned')->default(true);
            $table->boolean('email_task_updated')->default(false);
            $table->boolean('email_application_status')->default(true);
            $table->boolean('email_messages')->default(false);
            $table->boolean('email_announcements')->default(true);
            $table->boolean('email_reminders')->default(true);
            $table->boolean('email_schedule_changes')->default(true);
            $table->boolean('email_weekly_digest')->default(true);

            // Notification Types - SMS
            $table->boolean('sms_urgent_only')->default(true);
            $table->boolean('sms_task_assigned')->default(false);
            $table->boolean('sms_application_status')->default(false);
            $table->boolean('sms_reminders')->default(false);
            $table->boolean('sms_schedule_changes')->default(false);

            // Timing Preferences
            $table->time('quiet_hours_start')->default('22:00');
            $table->time('quiet_hours_end')->default('08:00');
            $table->boolean('respect_quiet_hours')->default(true);
            $table->json('notification_days')->nullable();

            // Frequency Settings
            $table->enum('digest_frequency', ['none', 'daily', 'weekly', 'monthly'])->default('weekly');
            $table->enum('reminder_frequency', ['none', 'once', 'daily', 'hourly'])->default('once');
            $table->integer('max_notifications_per_day')->default(50);

            // Advanced Settings
            $table->boolean('group_similar_notifications')->default(true);
            $table->integer('notification_retention_days')->default(30);
            $table->boolean('auto_mark_read_after_days')->default(false);
            $table->integer('auto_mark_read_days')->default(7);

            // Contact Information
            $table->string('preferred_email')->nullable();
            $table->string('preferred_phone')->nullable();
            $table->string('timezone')->default('Africa/Blantyre');
            $table->string('language')->default('en');

            $table->timestamps();

            // Indexes
            $table->unique('user_id');
            $table->index('notifications_enabled', 'idx_notifications_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
