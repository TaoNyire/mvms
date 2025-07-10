<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // login, logout, create, update, delete, security, system
            $table->string('action'); // specific action taken
            $table->string('entity_type')->nullable(); // User, Role, Organization, etc.
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of the affected entity
            $table->unsignedBigInteger('user_id')->nullable(); // User who performed the action
            $table->string('user_email')->nullable(); // Email for failed login attempts
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('details')->nullable(); // Additional details about the action
            $table->string('status'); // success, failed, warning, error
            $table->text('description'); // Human-readable description
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_logs');
    }
};
