<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('application_task_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'quit'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->json('work_evidence')->nullable(); // Photos, documents, etc.
            $table->timestamps();
            
            $table->index(['application_id', 'status']);
            $table->index(['status', 'completed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_task_status');
    }
};
