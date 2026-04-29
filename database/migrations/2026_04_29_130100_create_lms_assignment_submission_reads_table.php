<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_assignment_submission_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_assignment_submission_id')->constrained('lms_assignment_submissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['lms_assignment_submission_id', 'user_id'], 'lms_assignment_submission_reads_unique');
            $table->index(['user_id', 'last_read_at'], 'lms_assignment_submission_reads_user_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_assignment_submission_reads');
    }
};
