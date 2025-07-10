<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing null status values to 'pending'
        DB::table('organization_profiles')
            ->whereNull('status')
            ->update(['status' => 'pending']);

        // Then modify the column to ensure it has a proper default
        Schema::table('organization_profiles', function (Blueprint $table) {
            $table->enum('status', ['pending', 'verified', 'rejected'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_profiles', function (Blueprint $table) {
            $table->enum('status', ['pending', 'verified', 'rejected'])
                  ->nullable()
                  ->change();
        });
    }
};
