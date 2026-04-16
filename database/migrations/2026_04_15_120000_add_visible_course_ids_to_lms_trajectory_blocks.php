<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_trajectory_blocks', function (Blueprint $table) {
            $table->json('visible_course_ids')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('lms_trajectory_blocks', function (Blueprint $table) {
            $table->dropColumn('visible_course_ids');
        });
    }
};
