<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_to')->nullable();
            $table->string('unlock_type', 20)->default('date');
            $table->timestamps();

            $table->index(['lms_course_id', 'position']);
        });

        Schema::table('lms_course_stages', function (Blueprint $table) {
            $table->foreignId('lms_course_module_id')
                ->nullable()
                ->after('lms_course_id')
                ->constrained('lms_course_modules')
                ->nullOnDelete();
            $table->timestamp('available_from')->nullable()->after('is_locked');
            $table->integer('duration_minutes')->nullable()->after('available_from');
        });
    }

    public function down(): void
    {
        Schema::table('lms_course_stages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lms_course_module_id');
            $table->dropColumn(['available_from', 'duration_minutes']);
        });

        Schema::dropIfExists('lms_course_modules');
    }
};
