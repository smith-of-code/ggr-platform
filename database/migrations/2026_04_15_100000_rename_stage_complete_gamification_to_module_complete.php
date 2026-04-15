<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('lms_gamification_rules')
            ->where('action', 'stage_complete')
            ->update(['action' => 'module_complete']);

        DB::table('lms_gamification_rules')
            ->where('action', 'module_complete')
            ->whereIn('title', ['Прохождение этапа курса', 'Прохождение этапа'])
            ->update(['title' => 'Завершение модуля']);
    }

    public function down(): void
    {
        DB::table('lms_gamification_rules')
            ->where('action', 'module_complete')
            ->where('title', 'Завершение модуля')
            ->update(['title' => 'Прохождение этапа курса']);

        DB::table('lms_gamification_rules')
            ->where('action', 'module_complete')
            ->update(['action' => 'stage_complete']);
    }
};
