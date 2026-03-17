<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_videos', function (Blueprint $table) {
            $table->integer('duration_seconds')->nullable()->after('thumbnail');
        });

        Schema::table('lms_stage_progress', function (Blueprint $table) {
            $table->integer('watched_seconds')->default(0)->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('lms_videos', function (Blueprint $table) {
            $table->dropColumn('duration_seconds');
        });

        Schema::table('lms_stage_progress', function (Blueprint $table) {
            $table->dropColumn('watched_seconds');
        });
    }
};
