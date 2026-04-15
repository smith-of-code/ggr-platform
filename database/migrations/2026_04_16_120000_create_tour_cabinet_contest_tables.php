<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_cabinet_direction_cities', function (Blueprint $table) {
            $table->id();
            $table->string('project_key', 32);
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->boolean('needs_more_data')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['project_key', 'city_id']);
            $table->index('project_key');
        });

        Schema::create('tour_cabinet_contest_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('project_key', 32)->nullable();
            $table->json('selected_city_ids')->nullable();
            $table->unsignedTinyInteger('current_stage')->default(1);
            $table->text('stage3_text')->nullable();
            $table->string('stage3_video_url', 2048)->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('tour_cabinet_contest_city_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('lms_form_submission_id')->constrained('lms_form_submissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'city_id']);
        });

        Schema::create('tour_cabinet_contest_stage2_questions', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('project_key', 32)->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('tour_cabinet_contest_stage2_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')
                ->constrained('tour_cabinet_contest_stage2_questions')
                ->cascadeOnDelete();
            $table->text('answer_text');
            $table->timestamps();

            $table->unique(['user_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_cabinet_contest_stage2_answers');
        Schema::dropIfExists('tour_cabinet_contest_stage2_questions');
        Schema::dropIfExists('tour_cabinet_contest_city_submissions');
        Schema::dropIfExists('tour_cabinet_contest_progress');
        Schema::dropIfExists('tour_cabinet_direction_cities');
    }
};
