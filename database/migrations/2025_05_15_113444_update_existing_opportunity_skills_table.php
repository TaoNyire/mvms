<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('opportunity_skills', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('opportunity_skills', 'required_level')) {
                $table->enum('required_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate')->after('skill_id');
            }
            if (!Schema::hasColumn('opportunity_skills', 'is_required')) {
                $table->boolean('is_required')->default(false)->after('required_level');
            }
            if (!Schema::hasColumn('opportunity_skills', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down()
    {
        Schema::table('opportunity_skills', function (Blueprint $table) {
            $table->dropColumn(['required_level', 'is_required', 'created_at', 'updated_at']);
        });
    }
};
