<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_stage_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_course_stage_id')->constrained('lms_course_stages')->cascadeOnDelete();
            $table->string('type', 20)->default('content');
            $table->text('content')->nullable();
            $table->string('scorm_package')->nullable();
            $table->foreignId('lms_test_id')->nullable();
            $table->foreignId('lms_assignment_id')->nullable();
            $table->foreignId('lms_video_id')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['lms_course_stage_id', 'position']);
        });

        $stages = DB::table('lms_course_stages')->get();

        foreach ($stages as $stage) {
            DB::table('lms_stage_blocks')->insert([
                'lms_course_stage_id' => $stage->id,
                'type' => $stage->type ?? 'content',
                'content' => $stage->content,
                'scorm_package' => $stage->scorm_package,
                'lms_test_id' => $stage->lms_test_id,
                'lms_assignment_id' => $stage->lms_assignment_id,
                'lms_video_id' => $stage->lms_video_id,
                'position' => 0,
                'created_at' => $stage->created_at,
                'updated_at' => $stage->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_stage_blocks');
    }
};
