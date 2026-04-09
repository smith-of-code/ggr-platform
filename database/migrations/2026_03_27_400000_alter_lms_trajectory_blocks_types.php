<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE lms_trajectory_blocks DROP CONSTRAINT IF EXISTS lms_trajectory_blocks_type_check');
            DB::statement("DELETE FROM lms_trajectory_blocks WHERE type IN ('course', 'grant')");
            DB::statement("ALTER TABLE lms_trajectory_blocks ADD CONSTRAINT lms_trajectory_blocks_type_check CHECK (type IN ('static', 'task'))");
        }

        Schema::table('lms_trajectory_blocks', function (Blueprint $table) {
            $table->date('date_start')->nullable()->after('date_label');
            $table->date('date_end')->nullable()->after('date_start');
            $table->foreignId('lms_assignment_id')->nullable()->after('date_end')
                ->constrained('lms_assignments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lms_trajectory_blocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lms_assignment_id');
            $table->dropColumn(['date_start', 'date_end']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE lms_trajectory_blocks DROP CONSTRAINT IF EXISTS lms_trajectory_blocks_type_check');
            DB::statement("ALTER TABLE lms_trajectory_blocks ADD CONSTRAINT lms_trajectory_blocks_type_check CHECK (type IN ('static', 'course', 'grant'))");
        }
    }
};
