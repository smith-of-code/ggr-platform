<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_groups', function (Blueprint $table) {
            $table->json('linked_cities')->nullable()->after('title');
        });

        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE lms_gamification_points ALTER COLUMN user_id DROP NOT NULL');

        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->boolean('for_city_ranking_only')->default(false)->after('user_id');
            $table->string('city_name', 255)->nullable()->after('for_city_ranking_only');
            $table->foreignId('lms_group_id')->nullable()->after('city_name')->constrained('lms_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->dropForeign(['lms_group_id']);
            $table->dropColumn(['lms_group_id', 'city_name', 'for_city_ranking_only']);
        });

        DB::table('lms_gamification_points')->whereNull('user_id')->delete();

        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE lms_gamification_points ALTER COLUMN user_id SET NOT NULL');

        Schema::table('lms_gamification_points', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('lms_groups', function (Blueprint $table) {
            $table->dropColumn('linked_cities');
        });
    }
};
