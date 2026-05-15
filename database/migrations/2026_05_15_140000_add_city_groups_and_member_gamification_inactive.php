<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_groups', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('title')->constrained('cities')->nullOnDelete();
            $table->boolean('is_city_group')->default(false)->after('city_id');
            $table->unique(['lms_event_id', 'city_id'], 'lms_groups_event_city_unique');
        });

        Schema::table('lms_group_members', function (Blueprint $table) {
            $table->boolean('is_gamification_inactive')->default(false)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('lms_group_members', function (Blueprint $table) {
            $table->dropColumn('is_gamification_inactive');
        });

        Schema::table('lms_groups', function (Blueprint $table) {
            $table->dropUnique('lms_groups_event_city_unique');
            $table->dropForeign(['city_id']);
            $table->dropColumn(['city_id', 'is_city_group']);
        });
    }
};
