<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_trajectory_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_trajectory_id')->constrained('lms_trajectories')->cascadeOnDelete();
            $table->enum('type', ['static', 'course', 'grant']);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('date_label', 100)->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_trajectory_blocks');
    }
};
