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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');

            // Message Content
            $table->text('content');
            $table->enum('type', ['text', 'file', 'image', 'system', 'announcement'])->default('text');
            $table->json('attachments')->nullable(); // File attachments
            $table->json('metadata')->nullable(); // Additional message data

            // Threading
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->onDelete('set null');
            $table->integer('thread_depth')->default(0);

            // Status & Delivery
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->datetime('delivered_at')->nullable();
            $table->json('read_by')->nullable(); // Array of user IDs and timestamps
            $table->datetime('edited_at')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->datetime('deleted_at')->nullable();

            // Priority & Urgency
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_important')->default(false);
            $table->boolean('requires_response')->default(false);
            $table->datetime('response_deadline')->nullable();

            // Reactions & Interactions
            $table->json('reactions')->nullable(); // Emoji reactions
            $table->boolean('is_pinned')->default(false);
            $table->datetime('pinned_at')->nullable();
            $table->foreignId('pinned_by')->nullable()->constrained('users')->onDelete('set null');

            // System Messages
            $table->string('system_action')->nullable(); // user_joined, user_left, etc.
            $table->json('system_data')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['conversation_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index(['reply_to_id', 'thread_depth']);
            $table->index(['is_important', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
