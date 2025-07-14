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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('users')->onDelete('cascade');

            // Basic Information
            $table->string('title');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();

            // Skills and Categories
            $table->json('required_skills')->nullable(); // Array of skill IDs or names
            $table->string('category')->nullable(); // Education, Health, Environment, etc.
            $table->string('type')->default('one_time'); // one_time, recurring, ongoing
            $table->enum('urgency', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Location and Logistics
            $table->string('location_type')->default('physical'); // physical, remote, hybrid
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Timing
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('recurring_days')->nullable(); // For recurring opportunities
            $table->integer('duration_hours')->nullable();
            $table->datetime('application_deadline')->nullable();

            // Volunteer Requirements
            $table->integer('volunteers_needed')->default(1);
            $table->integer('volunteers_recruited')->default(0);
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->json('preferred_gender')->nullable(); // male, female, any
            $table->json('required_languages')->nullable();
            $table->boolean('requires_background_check')->default(false);
            $table->boolean('requires_training')->default(false);
            $table->text('training_details')->nullable();

            // Compensation and Benefits
            $table->boolean('is_paid')->default(false);
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('payment_frequency')->nullable(); // hourly, daily, total
            $table->boolean('provides_transport')->default(false);
            $table->boolean('provides_meals')->default(false);
            $table->boolean('provides_accommodation')->default(false);
            $table->text('other_benefits')->nullable();

            // Contact Information
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            // Status and Management
            $table->enum('status', ['draft', 'published', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->datetime('published_at')->nullable();
            $table->datetime('completed_at')->nullable();

            // Additional Information
            $table->json('tags')->nullable(); // Additional searchable tags
            $table->text('special_instructions')->nullable();
            $table->json('attachments')->nullable(); // File attachments
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['organization_id', 'status']);
            $table->index(['district', 'region']);
            $table->index(['category', 'type']);
            $table->index(['start_date', 'end_date']);
            $table->index(['status', 'published_at']);
            $table->index(['volunteers_needed', 'volunteers_recruited']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
