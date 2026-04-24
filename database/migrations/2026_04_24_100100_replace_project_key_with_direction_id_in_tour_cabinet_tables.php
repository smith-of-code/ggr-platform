<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'tour_cabinet_direction_cities',
        'tour_cabinet_contest_progress',
        'tour_cabinet_contest_stage2_questions',
        'tour_cabinet_contest_stage3_configs',
        'tour_cabinet_contest_direction_settings',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'project_key')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('direction_id')->nullable()->after('id');
                $blueprint->foreign('direction_id')->references('id')->on('directions')->nullOnDelete();
            });

            DB::table($table)
                ->whereNotNull('project_key')
                ->update([
                    'direction_id' => DB::raw(
                        "(SELECT id FROM directions WHERE directions.project_key = {$table}.project_key LIMIT 1)"
                    ),
                ]);

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('project_key');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'direction_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('project_key', 50)->nullable()->after('id');
            });

            DB::table($table)
                ->whereNotNull('direction_id')
                ->update([
                    'project_key' => DB::raw(
                        "(SELECT project_key FROM directions WHERE directions.id = {$table}.direction_id LIMIT 1)"
                    ),
                ]);

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['direction_id']);
                $blueprint->dropColumn('direction_id');
            });
        }
    }
};
