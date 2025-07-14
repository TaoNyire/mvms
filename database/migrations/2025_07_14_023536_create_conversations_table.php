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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Conversation Details
            $table->string('title')->nullable();
            $table->enum('type', ['direct', 'group', 'support', 'announcement'])->default('direct');
            $table->text('description')->nullable();

            // Participants
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->json('participant_ids'); // Array of user IDs
            $table->integer('participant_count')->default(2);

            // Related Context
            $table->string('related_type')->nullable(); // opportunity, task, assignment, etc.
            $table->unsignedBigInteger('related_id')->nullable();

            // Status & Settings
            $table->enum('status', ['active', 'archived', 'closed'])->default('active');
            $table->boolean('is_private')->default(true);
            $table->boolean('allow_file_sharing')->default(true);
            $table->boolean('notifications_enabled')->default(true);

            // Last Activity
            $table->unsignedBigInteger('last_message_id')->nullable();
            $table->datetime('last_activity_at')->nullable();
            $table->foreignId('last_activity_by')->nullable()->constrained('users')->onDelete('set null');

            // Metadata
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['type', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['related_type', 'related_id']);
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
