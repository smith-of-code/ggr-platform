<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_video_course_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_video_id')->constrained('lms_videos')->cascadeOnDelete();
            $table->foreignId('lms_course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lms_video_id', 'lms_course_id']);
        });

        Schema::dropIfExists('lms_video_access');
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_video_course_access');

        Schema::create('lms_video_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_video_id')->constrained('lms_videos')->cascadeOnDelete();
            $table->foreignId('lms_group_id')->constrained('lms_groups')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lms_video_id', 'lms_group_id']);
        });
    }
};
