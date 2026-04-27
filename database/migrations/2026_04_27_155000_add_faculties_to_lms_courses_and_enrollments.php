<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->json('faculties')->nullable()->after('unlocks_gamification');
        });

        Schema::table('lms_course_enrollments', function (Blueprint $table) {
            $table->string('faculty', 120)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lms_course_enrollments', function (Blueprint $table) {
            $table->dropColumn('faculty');
        });

        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropColumn('faculties');
        });
    }
};
