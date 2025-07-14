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
        Schema::table('organization_profiles', function (Blueprint $table) {
            // Add approval and rejection timestamps
            if (!Schema::hasColumn('organization_profiles', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('organization_profiles', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('organization_profiles', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->after('rejected_at');
            }
            if (!Schema::hasColumn('organization_profiles', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->constrained('users')->after('approved_by');
            }
            if (!Schema::hasColumn('organization_profiles', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_profiles', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'rejected_at', 'approved_by', 'rejected_by', 'rejection_reason']);
        });
    }
};
