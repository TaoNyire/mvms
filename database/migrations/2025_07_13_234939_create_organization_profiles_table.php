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
        if (Schema::hasTable('organization_profiles')) {
            Schema::table('organization_profiles', function (Blueprint $table) {
                // Add columns that might be missing from the existing table
                if (!Schema::hasColumn('organization_profiles', 'org_name')) {
                    $table->string('org_name')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('organization_profiles', 'registration_number')) {
                    $table->string('registration_number')->nullable()->after('org_name');
                }
                if (!Schema::hasColumn('organization_profiles', 'is_registered')) {
                    $table->boolean('is_registered')->default(true)->after('registration_number');
                }
                if (!Schema::hasColumn('organization_profiles', 'org_type')) {
                    $table->string('org_type')->nullable()->after('is_registered');
                }
                if (!Schema::hasColumn('organization_profiles', 'sector')) {
                    $table->string('sector')->nullable()->after('org_type');
                }
                if (!Schema::hasColumn('organization_profiles', 'focus_areas')) {
                    $table->json('focus_areas')->nullable()->after('sector');
                }
                if (!Schema::hasColumn('organization_profiles', 'description')) {
                    $table->text('description')->nullable()->after('focus_areas');
                }
                if (!Schema::hasColumn('organization_profiles', 'mission')) {
                    $table->text('mission')->nullable()->after('description');
                }
                if (!Schema::hasColumn('organization_profiles', 'vision')) {
                    $table->text('vision')->nullable()->after('mission');
                }
                if (!Schema::hasColumn('organization_profiles', 'physical_address')) {
                    $table->text('physical_address')->nullable()->after('vision');
                }
                if (!Schema::hasColumn('organization_profiles', 'district')) {
                    $table->string('district')->nullable()->after('physical_address');
                }
                if (!Schema::hasColumn('organization_profiles', 'region')) {
                    $table->string('region')->nullable()->after('district');
                }
                if (!Schema::hasColumn('organization_profiles', 'email')) {
                    $table->string('email')->nullable()->after('region');
                }
                if (!Schema::hasColumn('organization_profiles', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (!Schema::hasColumn('organization_profiles', 'website')) {
                    $table->string('website')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('organization_profiles', 'is_complete')) {
                    $table->boolean('is_complete')->default(false)->after('website');
                }
                if (!Schema::hasColumn('organization_profiles', 'profile_completed_at')) {
                    $table->timestamp('profile_completed_at')->nullable()->after('is_complete');
                }
            });
        } else {
            // Create the table if it doesn't exist
            Schema::create('organization_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                // Basic Organization Information
                $table->string('org_name');
                $table->text('description')->nullable();
                $table->text('mission')->nullable();
                $table->text('vision')->nullable();
                $table->string('sector')->nullable();
                $table->string('org_type')->nullable(); // NGO, CBO, Government, Private, etc.

                // Registration Information
                $table->string('registration_number')->nullable();
                $table->boolean('is_registered')->default(true);
                $table->date('registration_date')->nullable();
                $table->string('registration_authority')->nullable(); // Ministry, Council, etc.
                $table->string('tax_id')->nullable();

                // Contact Information
                $table->text('physical_address')->nullable();
                $table->string('district')->nullable();
                $table->string('region')->nullable();
                $table->string('postal_address')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('alternative_phone')->nullable();
                $table->string('website')->nullable();
                $table->string('social_media')->nullable(); // JSON for multiple platforms

                // Operational Information
                $table->json('focus_areas')->nullable(); // Areas of work
                $table->json('target_beneficiaries')->nullable(); // Who they serve
                $table->json('geographical_coverage')->nullable(); // Areas they operate
                $table->integer('staff_count')->nullable();
                $table->integer('volunteer_count')->nullable();
                $table->decimal('annual_budget', 15, 2)->nullable();
                $table->date('established_date')->nullable();

                // Capacity and Resources
                $table->json('services_offered')->nullable();
                $table->json('resources_available')->nullable();
                $table->json('partnerships')->nullable();
                $table->text('achievements')->nullable();
                $table->text('current_projects')->nullable();

                // Documents
                $table->string('registration_certificate_path')->nullable();
                $table->string('registration_certificate_original_name')->nullable();
                $table->string('tax_clearance_path')->nullable();
                $table->string('tax_clearance_original_name')->nullable();
                $table->json('other_documents')->nullable();

                // Contact Person Information
                $table->string('contact_person_name')->nullable();
                $table->string('contact_person_title')->nullable();
                $table->string('contact_person_phone')->nullable();
                $table->string('contact_person_email')->nullable();

                // Profile Status
                $table->boolean('is_complete')->default(false);
                $table->boolean('is_verified')->default(false);
                $table->boolean('active')->default(true);
                $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
                $table->timestamp('profile_completed_at')->nullable();
                $table->timestamp('verified_at')->nullable();

                // Additional Information
                $table->text('additional_info')->nullable();
                $table->json('certifications')->nullable();
                $table->text('volunteer_requirements')->nullable();

                $table->timestamps();

                // Indexes
                $table->index(['user_id']);
                $table->index(['district', 'region']);
                $table->index(['is_complete', 'active', 'status']);
                $table->index(['sector', 'org_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_profiles');
    }
};
