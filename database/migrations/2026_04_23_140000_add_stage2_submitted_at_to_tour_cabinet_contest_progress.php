<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_cabinet_contest_progress', function (Blueprint $table) {
            $table->timestamp('stage2_submitted_at')->nullable()->after('current_stage');
        });

        DB::table('tour_cabinet_contest_progress as p')
            ->where('current_stage', '>=', 3)
            ->whereNull('stage2_submitted_at')
            ->whereExists(function ($q): void {
                $q->selectRaw('1')
                    ->from('tour_cabinet_contest_stage2_answers as a')
                    ->whereColumn('a.user_id', 'p.user_id');
            })
            ->update(['stage2_submitted_at' => DB::raw('p.updated_at')]);
    }

    public function down(): void
    {
        Schema::table('tour_cabinet_contest_progress', function (Blueprint $table) {
            $table->dropColumn('stage2_submitted_at');
        });
    }
};
