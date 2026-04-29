<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_cabinet_commerce_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('current_stage')->default(1);
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('tour_id')->nullable()->constrained('tours')->nullOnDelete();
            $table->foreignId('lms_form_submission_id')->nullable()->constrained('lms_form_submissions')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_cabinet_commerce_progress');
    }
};
