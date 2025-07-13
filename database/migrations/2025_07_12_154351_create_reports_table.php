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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('users')->onDelete('cascade');
            $table->enum('report_type', ['completed_tasks', 'failed_tasks', 'current_volunteers', 'comprehensive']);
            $table->integer('month'); // 1-12
            $table->integer('year'); // e.g., 2024
            $table->json('data')->nullable(); // Store report data
            $table->timestamp('generated_at')->nullable();
            $table->string('file_path')->nullable(); // For PDF storage
            $table->timestamps();

            // Indexes for better performance
            $table->index(['organization_id', 'report_type']);
            $table->index(['organization_id', 'month', 'year']);
            $table->index(['report_type', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
