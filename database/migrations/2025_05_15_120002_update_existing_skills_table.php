<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('skills', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('skills', 'category')) {
                $table->string('category')->default('general')->after('name');
            }
            if (!Schema::hasColumn('skills', 'description')) {
                $table->text('description')->nullable()->after('category');
            }
            if (!Schema::hasColumn('skills', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    public function down()
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn(['category', 'description', 'is_active']);
        });
    }
};
