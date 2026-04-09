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

        DB::statement('ALTER TABLE lms_course_enrollments DROP CONSTRAINT IF EXISTS lms_course_enrollments_status_check');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE lms_course_enrollments ADD CONSTRAINT lms_course_enrollments_status_check CHECK (status::text = ANY (ARRAY['enrolled'::text, 'in_progress'::text, 'completed'::text]))");
    }
};
