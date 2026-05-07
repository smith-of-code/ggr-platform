<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->unsignedInteger('gamification_points')->default(0)->after('is_active');
        });

        Schema::table('lms_tests', function (Blueprint $table) {
            $table->unsignedInteger('gamification_points')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->dropColumn('gamification_points');
        });

        Schema::table('lms_tests', function (Blueprint $table) {
            $table->dropColumn('gamification_points');
        });
    }
};
