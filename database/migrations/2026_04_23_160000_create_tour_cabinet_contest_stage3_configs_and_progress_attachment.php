<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_cabinet_contest_stage3_configs', function (Blueprint $table) {
            $table->id();
            $table->string('project_key', 32)->unique();
            $table->string('title', 500);
            $table->text('task_body')->nullable();
            $table->string('response_format', 32)->default('video_link');
            $table->timestamps();
        });

        Schema::table('tour_cabinet_contest_progress', function (Blueprint $table) {
            $table->string('stage3_attachment_path', 2048)->nullable()->after('stage3_video_url');
            $table->string('stage3_attachment_original_name', 512)->nullable()->after('stage3_attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('tour_cabinet_contest_progress', function (Blueprint $table) {
            $table->dropColumn(['stage3_attachment_path', 'stage3_attachment_original_name']);
        });

        Schema::dropIfExists('tour_cabinet_contest_stage3_configs');
    }
};
