<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_videos', function (Blueprint $table) {
            $table->boolean('visible_to_all')->default(true)->after('is_active');
        });

        DB::table('lms_videos')
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('lms_video_access')
                    ->whereColumn('lms_video_access.lms_video_id', 'lms_videos.id');
            })
            ->update(['visible_to_all' => false]);
    }

    public function down(): void
    {
        Schema::table('lms_videos', function (Blueprint $table) {
            $table->dropColumn('visible_to_all');
        });
    }
};
