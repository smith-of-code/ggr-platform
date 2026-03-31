<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->string('template_file_name', 255)->nullable()->after('template_file');
        });

        Schema::create('lms_assignment_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_assignment_id')->constrained('lms_assignments')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('response_type', 20)->default('file');
            $table->string('template_file', 500)->nullable();
            $table->string('template_file_name', 255)->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_submission_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_assignment_submission_id')->constrained('lms_assignment_submissions')->cascadeOnDelete();
            $table->foreignId('lms_assignment_task_id')->constrained('lms_assignment_tasks')->cascadeOnDelete();
            $table->text('text_content')->nullable();
            $table->string('link', 500)->nullable();
            $table->jsonb('files')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_submission_answers');
        Schema::dropIfExists('lms_assignment_tasks');

        Schema::table('lms_assignments', function (Blueprint $table) {
            $table->dropColumn('template_file_name');
        });
    }
};
