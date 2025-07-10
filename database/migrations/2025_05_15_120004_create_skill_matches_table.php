<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('skill_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->decimal('match_score', 5, 2); // 0.00 to 100.00
            $table->json('matched_skills'); // Array of matched skill details
            $table->json('missing_skills')->nullable(); // Array of missing required skills
            $table->boolean('is_notified')->default(false); // Whether user has been notified
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'opportunity_id']);
            $table->index(['match_score', 'calculated_at']);
            $table->index(['user_id', 'match_score']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('skill_matches');
    }
};
