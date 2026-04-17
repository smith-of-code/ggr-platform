<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE lms_course_stages DROP CONSTRAINT IF EXISTS lms_course_stages_type_check');

        DB::statement("ALTER TABLE lms_course_stages ADD CONSTRAINT lms_course_stages_type_check CHECK (type IN ('content', 'scorm', 'test', 'assignment', 'video', 'workshop', 'city_meeting', 'curator_meeting', 'file'))");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE lms_course_stages DROP CONSTRAINT IF EXISTS lms_course_stages_type_check');

        DB::statement("ALTER TABLE lms_course_stages ADD CONSTRAINT lms_course_stages_type_check CHECK (type IN ('content', 'scorm', 'test', 'assignment', 'video', 'workshop', 'city_meeting', 'curator_meeting'))");
    }
};
