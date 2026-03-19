<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_course_modules', function (Blueprint $table) {
            $table->foreignId('source_module_id')
                ->nullable()
                ->after('unlock_type')
                ->constrained('lms_course_modules')
                ->nullOnDelete();
        });

        Schema::table('lms_course_stages', function (Blueprint $table) {
            $table->foreignId('source_stage_id')
                ->nullable()
                ->after('duration_minutes')
                ->constrained('lms_course_stages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lms_course_stages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_stage_id');
        });

        Schema::table('lms_course_modules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_module_id');
        });
    }
};
