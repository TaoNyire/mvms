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
        Schema::table('volunteer_profiles', function (Blueprint $table) {
            //
            $table->string('cv')->nullable(); // stored path
            $table->string('cv_original_name')->nullable(); // original file name
            $table->string('qualifications')->nullable();
            $table->string('qualifications_original_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_profiles', function (Blueprint $table) {
            //
        });
    }
};
