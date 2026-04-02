<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE lms_course_stages DROP CONSTRAINT IF EXISTS lms_course_stages_type_check");

        DB::statement("ALTER TABLE lms_course_stages ADD CONSTRAINT lms_course_stages_type_check CHECK (type IN ('content', 'scorm', 'test', 'assignment', 'video', 'workshop', 'city_meeting', 'curator_meeting'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE lms_course_stages DROP CONSTRAINT IF EXISTS lms_course_stages_type_check");

        DB::statement("ALTER TABLE lms_course_stages ADD CONSTRAINT lms_course_stages_type_check CHECK (type IN ('content', 'scorm', 'test', 'assignment', 'video'))");
    }
};
