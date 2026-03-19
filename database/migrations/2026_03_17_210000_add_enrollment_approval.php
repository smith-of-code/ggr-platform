<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->after('is_active');
        });

        // Change enum to varchar to support pending/rejected
        DB::statement("ALTER TABLE lms_course_enrollments ALTER COLUMN status TYPE varchar(20)");
        DB::statement("ALTER TABLE lms_course_enrollments ALTER COLUMN status SET DEFAULT 'enrolled'");
        DB::statement("DROP TYPE IF EXISTS lms_course_enrollments_status_check");

        Schema::table('lms_course_enrollments', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable()->after('completed_at');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lms_course_enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('reviewed_at');
        });

        DB::statement("UPDATE lms_course_enrollments SET status = 'enrolled' WHERE status NOT IN ('enrolled', 'in_progress', 'completed')");

        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropColumn('requires_approval');
        });
    }
};
