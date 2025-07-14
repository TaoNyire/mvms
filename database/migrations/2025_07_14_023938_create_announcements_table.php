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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Announcement Content
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['general', 'urgent', 'event', 'policy', 'system'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Targeting
            $table->enum('audience', ['all', 'volunteers', 'organizations', 'admins', 'custom'])->default('all');
            $table->json('target_user_ids')->nullable(); // Specific users for custom audience
            $table->json('target_roles')->nullable(); // Specific roles
            $table->json('target_locations')->nullable(); // Specific districts/regions

            // Scheduling
            $table->datetime('published_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_scheduled')->default(false);
            $table->boolean('auto_expire')->default(false);

            // Status & Visibility
            $table->enum('status', ['draft', 'published', 'archived', 'expired'])->default('draft');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->boolean('send_notification')->default(true);

            // Engagement
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->json('viewed_by')->nullable(); // Array of user IDs who viewed
            $table->json('liked_by')->nullable(); // Array of user IDs who liked

            // Media & Attachments
            $table->string('featured_image')->nullable();
            $table->json('attachments')->nullable();
            $table->string('external_link')->nullable();
            $table->string('external_link_text')->nullable();

            // Categorization
            $table->json('tags')->nullable();
            $table->string('category')->nullable();
            $table->string('color')->default('#007bff');
            $table->string('icon')->nullable();

            // Related Entities
            $table->string('related_type')->nullable(); // opportunity, task, etc.
            $table->unsignedBigInteger('related_id')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['status', 'published_at']);
            $table->index(['audience', 'status']);
            $table->index(['type', 'priority']);
            $table->index(['is_pinned', 'is_featured']);
            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
