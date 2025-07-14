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
        Schema::table('users', function (Blueprint $table) {
            // Account status management
            $table->boolean('is_active')->default(true)->after('email_verified_at');
            $table->enum('account_status', ['active', 'inactive', 'suspended', 'pending_approval'])->default('active')->after('is_active');
            $table->text('status_reason')->nullable()->after('account_status');

            // Admin management fields
            $table->foreignId('activated_by')->nullable()->constrained('users')->onDelete('set null')->after('status_reason');
            $table->datetime('activated_at')->nullable()->after('activated_by');
            $table->foreignId('deactivated_by')->nullable()->constrained('users')->onDelete('set null')->after('activated_at');
            $table->datetime('deactivated_at')->nullable()->after('deactivated_by');

            // Last activity tracking
            $table->datetime('last_login_at')->nullable()->after('deactivated_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');

            // Admin notes
            $table->text('admin_notes')->nullable()->after('last_login_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'account_status',
                'status_reason',
                'activated_by',
                'activated_at',
                'deactivated_by',
                'deactivated_at',
                'last_login_at',
                'last_login_ip',
                'admin_notes'
            ]);
        });
    }
};
