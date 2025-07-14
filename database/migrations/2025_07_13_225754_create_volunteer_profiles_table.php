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
        // Check if table already exists, if so, add missing columns
        if (Schema::hasTable('volunteer_profiles')) {
            Schema::table('volunteer_profiles', function (Blueprint $table) {
                // Add columns that might be missing from the existing table
                if (!Schema::hasColumn('volunteer_profiles', 'full_name')) {
                    $table->string('full_name')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable()->after('full_name');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'gender')) {
                    $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->after('date_of_birth');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'phone')) {
                    $table->string('phone')->nullable()->after('gender');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'emergency_contact_name')) {
                    $table->string('emergency_contact_name')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'emergency_contact_phone')) {
                    $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'physical_address')) {
                    $table->string('physical_address')->nullable()->after('emergency_contact_phone');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'skills')) {
                    $table->json('skills')->nullable()->after('region');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'available_days')) {
                    $table->json('available_days')->nullable()->after('skills');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'availability_type')) {
                    $table->enum('availability_type', ['full_time', 'part_time', 'weekends', 'flexible'])->nullable()->after('available_days');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'education_level')) {
                    $table->string('education_level')->nullable()->after('availability_type');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'motivation')) {
                    $table->text('motivation')->nullable()->after('education_level');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'is_complete')) {
                    $table->boolean('is_complete')->default(false)->after('motivation');
                }
                if (!Schema::hasColumn('volunteer_profiles', 'profile_completed_at')) {
                    $table->timestamp('profile_completed_at')->nullable()->after('is_complete');
                }
            });
        } else {
            // Create the table if it doesn't exist
            Schema::create('volunteer_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                // Basic Information
                $table->string('full_name')->nullable();
                $table->text('bio')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();

                // Contact Details
                $table->string('phone')->nullable();
                $table->string('alternative_phone')->nullable();
                $table->string('emergency_contact_name')->nullable();
                $table->string('emergency_contact_phone')->nullable();

                // Location Information
                $table->string('physical_address')->nullable();
                $table->string('district')->nullable();
                $table->string('region')->nullable();
                $table->string('postal_code')->nullable();

                // Skills and Interests
                $table->json('skills')->nullable();
                $table->json('interests')->nullable();
                $table->text('experience_description')->nullable();
                $table->json('languages')->nullable();

                // Availability
                $table->json('available_days')->nullable();
                $table->time('available_time_start')->nullable();
                $table->time('available_time_end')->nullable();
                $table->json('preferred_locations')->nullable();
                $table->boolean('can_travel')->default(false);
                $table->integer('max_travel_distance')->nullable();
                $table->enum('availability_type', ['full_time', 'part_time', 'weekends', 'flexible'])->nullable();

                // Documents
                $table->string('id_document_path')->nullable();
                $table->string('id_document_original_name')->nullable();
                $table->string('cv_path')->nullable();
                $table->string('cv_original_name')->nullable();
                $table->json('certificates')->nullable();

                // Education and Qualifications
                $table->string('education_level')->nullable();
                $table->string('field_of_study')->nullable();
                $table->string('institution')->nullable();
                $table->year('graduation_year')->nullable();

                // Professional Information
                $table->string('current_occupation')->nullable();
                $table->string('employer')->nullable();
                $table->text('professional_skills')->nullable();

                // Volunteer Preferences
                $table->json('preferred_volunteer_types')->nullable();
                $table->json('causes_interested_in')->nullable();
                $table->boolean('has_volunteered_before')->default(false);
                $table->text('previous_volunteer_experience')->nullable();

                // Profile Status
                $table->boolean('is_complete')->default(false);
                $table->boolean('is_verified')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('profile_completed_at')->nullable();
                $table->timestamp('last_updated_at')->nullable();

                // Additional Information
                $table->text('special_requirements')->nullable();
                $table->text('motivation')->nullable();
                $table->json('references')->nullable();

                $table->timestamps();

                // Indexes
                $table->index(['user_id']);
                $table->index(['district', 'region']);
                $table->index(['is_complete', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_profiles');
    }
};
