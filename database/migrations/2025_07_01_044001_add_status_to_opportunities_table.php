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
        Schema::table('opportunities', function (Blueprint $table) {
            //
            
        $table->string('status')->default('pending')->after('volunteers_needed');
        // Or use enum if you want to restrict values
        // $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->after('volunteers_needed')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            //
        });
    }
};
