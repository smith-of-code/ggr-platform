<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->string('source_type', 64)->nullable()->after('lms_gamification_rule_id');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
        });

        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->index(['lms_event_id', 'user_id', 'source_type', 'source_id'], 'lms_gamification_points_source_idx');
        });
    }

    public function down(): void
    {
        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->dropIndex('lms_gamification_points_source_idx');
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
