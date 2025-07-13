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
        // First, update any existing status values to match new enum
        DB::table('opportunities')
            ->where('status', 'pending')
            ->update(['status' => 'active']);

        DB::table('opportunities')
            ->where('status', 'in_progress')
            ->update(['status' => 'recruitment_closed']);

        // Modify the status column to use new enum values
        Schema::table('opportunities', function (Blueprint $table) {
            $table->enum('status', ['active', 'recruitment_closed', 'in_progress', 'completed', 'cancelled'])
                  ->default('active')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
