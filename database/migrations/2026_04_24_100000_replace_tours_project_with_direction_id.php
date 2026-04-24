<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->unsignedBigInteger('direction_id')->nullable()->after('duration');
            $table->foreign('direction_id')->references('id')->on('directions')->nullOnDelete();
        });

        DB::table('tours')
            ->whereNotNull('project')
            ->update([
                'direction_id' => DB::raw(
                    '(SELECT id FROM directions WHERE directions.project_key = tours.project LIMIT 1)'
                ),
            ]);

        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn('project');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->string('project')->nullable()->after('duration');
        });

        DB::table('tours')
            ->whereNotNull('direction_id')
            ->update([
                'project' => DB::raw(
                    '(SELECT project_key FROM directions WHERE directions.id = tours.direction_id LIMIT 1)'
                ),
            ]);

        Schema::table('tours', function (Blueprint $table) {
            $table->dropForeign(['direction_id']);
            $table->dropColumn('direction_id');
        });
    }
};
