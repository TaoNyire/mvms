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
        Schema::table('skills', function (Blueprint $table) {
            // Add organization_id to link skills to specific organizations (nullable for global skills)
            $table->foreignId('organization_id')->nullable()->constrained('users')->onDelete('cascade');

            // Add skill type to distinguish between global and organization-specific skills
            $table->enum('skill_type', ['global', 'organization_specific'])->default('global');

            // Add proficiency level requirement for organization skills
            $table->enum('required_proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable();

            // Add priority for ordering skills within an organization
            $table->integer('priority')->default(0);

            // Add indexes for better performance
            $table->index(['organization_id', 'skill_type'], 'skills_org_type_index');
            $table->index(['organization_id', 'is_active'], 'skills_org_active_index');
            $table->index(['skill_type', 'is_active'], 'skills_type_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropIndex('skills_org_type_index');
            $table->dropIndex('skills_org_active_index');
            $table->dropIndex('skills_type_active_index');
            $table->dropForeign(['organization_id']);
            $table->dropColumn([
                'organization_id',
                'skill_type',
                'required_proficiency_level',
                'priority'
            ]);
        });
    }
};
