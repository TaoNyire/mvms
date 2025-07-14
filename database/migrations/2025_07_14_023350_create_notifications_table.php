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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Notification Details
            $table->string('type'); // task_assigned, application_status, message_received, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the notification

            // Status & Timing
            $table->enum('status', ['unread', 'read', 'archived'])->default('unread');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->datetime('read_at')->nullable();
            $table->datetime('archived_at')->nullable();

            // Related Entities
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('related_type')->nullable(); // opportunity, task, assignment, etc.
            $table->unsignedBigInteger('related_id')->nullable();

            // Delivery Channels
            $table->boolean('sent_in_app')->default(true);
            $table->boolean('sent_email')->default(false);
            $table->boolean('sent_sms')->default(false);
            $table->boolean('sent_push')->default(false);

            // Delivery Status
            $table->datetime('email_sent_at')->nullable();
            $table->datetime('sms_sent_at')->nullable();
            $table->datetime('push_sent_at')->nullable();
            $table->boolean('email_failed')->default(false);
            $table->boolean('sms_failed')->default(false);
            $table->boolean('push_failed')->default(false);

            // Action & Navigation
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->json('action_data')->nullable();

            // Metadata
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_system')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['type', 'created_at']);
            $table->index(['related_type', 'related_id']);
            $table->index(['priority', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
