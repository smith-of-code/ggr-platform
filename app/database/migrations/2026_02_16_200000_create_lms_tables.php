<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Events: each ВШГР event has its own set of users/courses ──
        Schema::create('lms_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('auth_method', ['email', 'sso'])->default('email');
            $table->string('sso_provider_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── LMS User profiles (extends users table) ──
        Schema::create('lms_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->enum('role', ['participant', 'curator', 'leader', 'admin'])->default('participant');
            $table->string('position')->nullable();
            $table->string('city')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'lms_event_id']);
        });

        // ── Groups ──
        Schema::create('lms_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('curator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lms_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_group_id')->constrained('lms_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lms_group_id', 'user_id']);
        });

        // ── Courses ──
        Schema::create('lms_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('sequential')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_course_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['content', 'scorm', 'test', 'assignment', 'video'])->default('content');
            $table->text('content')->nullable();
            $table->string('scorm_package')->nullable();
            $table->foreignId('lms_test_id')->nullable();
            $table->foreignId('lms_assignment_id')->nullable();
            $table->foreignId('lms_video_id')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'in_progress', 'completed'])->default('enrolled');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['lms_course_id', 'user_id']);
        });

        Schema::create('lms_stage_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_course_stage_id')->constrained('lms_course_stages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->json('scorm_data')->nullable();
            $table->integer('score')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['lms_course_stage_id', 'user_id']);
        });

        // ── Knowledge Base ──
        Schema::create('lms_kb_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('lms_kb_sections')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('in_menu')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_kb_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_kb_section_id')->constrained('lms_kb_sections')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['text', 'video', 'link', 'file'])->default('text');
            $table->text('content')->nullable();
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_kb_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_kb_section_id')->constrained('lms_kb_sections')->cascadeOnDelete();
            $table->foreignId('lms_group_id')->constrained('lms_groups')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lms_kb_section_id', 'lms_group_id']);
        });

        // ── Tests ──
        Schema::create('lms_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_answers')->default(true);
            $table->boolean('show_correct_answers')->default(false);
            $table->integer('passing_score')->default(60);
            $table->integer('max_attempts')->nullable();
            $table->boolean('in_menu')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lms_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_test_id')->constrained('lms_tests')->cascadeOnDelete();
            $table->text('question');
            $table->enum('type', ['single', 'multiple', 'text'])->default('single');
            $table->integer('points')->default(1);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_test_question_id')->constrained('lms_test_questions')->cascadeOnDelete();
            $table->text('answer');
            $table->boolean('is_correct')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_test_id')->constrained('lms_tests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->integer('max_score')->default(0);
            $table->integer('percentage')->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lms_test_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_test_attempt_id')->constrained('lms_test_attempts')->cascadeOnDelete();
            $table->foreignId('lms_test_question_id')->constrained('lms_test_questions')->cascadeOnDelete();
            $table->json('selected_answer_ids')->nullable();
            $table->text('text_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->integer('points_earned')->default(0);
            $table->timestamps();
        });

        // ── Assignments ──
        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('template_file')->nullable();
            $table->enum('completion_mode', ['on_submit', 'on_review'])->default('on_review');
            $table->timestamp('deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lms_assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_assignment_id')->constrained('lms_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('text_content')->nullable();
            $table->string('link')->nullable();
            $table->json('files')->nullable();
            $table->enum('status', ['draft', 'submitted', 'revision', 'approved', 'rejected'])->default('draft');
            $table->timestamps();
        });

        Schema::create('lms_assignment_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_assignment_submission_id')->constrained('lms_assignment_submissions')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment')->nullable();
            $table->json('files')->nullable();
            $table->enum('decision', ['approve', 'revision', 'reject']);
            $table->timestamps();
        });

        // ── Trajectories ──
        Schema::create('lms_trajectories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lms_trajectory_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_trajectory_id')->constrained('lms_trajectories')->cascadeOnDelete();
            $table->foreignId('lms_course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('opens_at')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_trajectory_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_trajectory_id')->constrained('lms_trajectories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'in_progress', 'completed'])->default('enrolled');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['lms_trajectory_id', 'user_id']);
        });

        // ── Videos ──
        Schema::create('lms_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('source', ['upload', 'rutube', 'link'])->default('link');
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_recording')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lms_video_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_video_id')->constrained('lms_videos')->cascadeOnDelete();
            $table->foreignId('lms_group_id')->constrained('lms_groups')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lms_video_id', 'lms_group_id']);
        });

        // ── Materials ──
        Schema::create('lms_material_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->text('content')->nullable();
            $table->boolean('in_menu')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_material_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_material_section_id')->constrained('lms_material_sections')->cascadeOnDelete();
            $table->foreignId('lms_group_id')->constrained('lms_groups')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lms_material_section_id', 'lms_group_id']);
        });

        // ── Gamification ──
        Schema::create('lms_gamification_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->string('action');
            $table->integer('points')->default(0);
            $table->integer('max_times')->nullable();
            $table->boolean('is_auto')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lms_gamification_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lms_gamification_rule_id')->nullable()->constrained('lms_gamification_rules')->nullOnDelete();
            $table->integer('points');
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // ── Indexes for performance ──
        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->index(['lms_event_id', 'user_id', 'points']);
        });

        Schema::table('lms_course_enrollments', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });

        Schema::table('lms_stage_progress', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        $tables = [
            'lms_gamification_points', 'lms_gamification_rules',
            'lms_material_access', 'lms_material_sections',
            'lms_video_access', 'lms_videos',
            'lms_trajectory_enrollments', 'lms_trajectory_steps', 'lms_trajectories',
            'lms_assignment_reviews', 'lms_assignment_submissions', 'lms_assignments',
            'lms_test_responses', 'lms_test_attempts', 'lms_test_answers', 'lms_test_questions', 'lms_tests',
            'lms_kb_access', 'lms_kb_items', 'lms_kb_sections',
            'lms_stage_progress', 'lms_course_enrollments', 'lms_course_stages', 'lms_courses',
            'lms_group_members', 'lms_groups',
            'lms_profiles', 'lms_events',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
