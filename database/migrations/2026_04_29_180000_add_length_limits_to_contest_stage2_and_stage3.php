<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_cabinet_contest_stage2_questions', function (Blueprint $table): void {
            $table->unsignedInteger('min_length')->nullable()->after('is_active');
            $table->unsignedInteger('max_length')->nullable()->after('min_length');
        });

        Schema::table('tour_cabinet_contest_stage3_configs', function (Blueprint $table): void {
            $table->unsignedInteger('text_min_length')->nullable()->after('response_format');
            $table->unsignedInteger('text_max_length')->nullable()->after('text_min_length');
        });
    }

    public function down(): void
    {
        Schema::table('tour_cabinet_contest_stage2_questions', function (Blueprint $table): void {
            $table->dropColumn(['min_length', 'max_length']);
        });

        Schema::table('tour_cabinet_contest_stage3_configs', function (Blueprint $table): void {
            $table->dropColumn(['text_min_length', 'text_max_length']);
        });
    }
};
