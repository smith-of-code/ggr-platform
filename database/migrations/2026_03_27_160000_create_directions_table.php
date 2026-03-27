<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('project_key', 50)->nullable();

            $table->string('sub_directions_title')->nullable();
            $table->text('sub_directions_description')->nullable();
            $table->json('sub_directions')->nullable();

            $table->json('target_audiences')->nullable();
            $table->text('target_audience_note')->nullable();

            $table->json('free_participation_steps')->nullable();
            $table->json('free_participation_details')->nullable();
            $table->json('paid_participation_steps')->nullable();

            $table->json('featured_tour_ids')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directions');
    }
};
