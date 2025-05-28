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
        // 5. Organization Profiles Table
        Schema::create('organization_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('org_name');
            $table->text('description')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->string('sector')->nullable();
            $table->enum('org_type', ['NGO', 'CBO', 'Government', 'Faith-based', 'Educational', 'Private'])->nullable();
            $table->string('registration_number')->nullable();
            $table->boolean('is_registered')->default(true);
            $table->string('physical_address')->nullable();
            $table->string('district')->nullable();
            $table->string('region')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('focus_areas')->nullable();
            $table->boolean('active')->default(true);
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_profiles');
    }
};
