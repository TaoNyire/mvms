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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->foreignId('volunteer_id')->constrained('users')->onDelete('cascade');

            // Application Details
            $table->text('message')->nullable(); // Cover letter/motivation
            $table->text('relevant_experience')->nullable();
            $table->json('availability_details')->nullable(); // Specific availability for this opportunity
            $table->boolean('agrees_to_terms')->default(false);

            // Status and Management
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn', 'completed'])->default('pending');
            $table->text('organization_notes')->nullable(); // Internal notes by organization
            $table->text('rejection_reason')->nullable();
            $table->text('feedback')->nullable(); // Post-completion feedback
            $table->integer('rating')->nullable(); // 1-5 star rating

            // Timestamps
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Communication
            $table->boolean('email_sent')->default(false);
            $table->timestamp('last_email_sent_at')->nullable();
            $table->json('email_history')->nullable(); // Track email communications

            // Additional Information
            $table->json('custom_responses')->nullable(); // Responses to custom questions
            $table->boolean('background_check_completed')->default(false);
            $table->boolean('training_completed')->default(false);
            $table->text('special_requirements')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['opportunity_id', 'status']);
            $table->index(['volunteer_id', 'status']);
            $table->index(['status', 'applied_at']);
            $table->unique(['opportunity_id', 'volunteer_id']); // Prevent duplicate applications
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
