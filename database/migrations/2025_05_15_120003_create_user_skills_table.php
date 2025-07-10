<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->integer('years_experience')->nullable();
            $table->text('notes')->nullable(); // Additional notes about the skill
            $table->timestamps();
            
            $table->unique(['user_id', 'skill_id']);
            $table->index(['user_id', 'proficiency_level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_skills');
    }
};
