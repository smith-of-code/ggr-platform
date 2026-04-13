<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->renameColumn('hero_bg_color', 'hero_bg_color_from');
        });

        Schema::table('directions', function (Blueprint $table) {
            $table->string('hero_bg_color_via', 20)->nullable()->after('hero_bg_color_from');
            $table->string('hero_bg_color_to', 20)->nullable()->after('hero_bg_color_via');
        });
    }

    public function down(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->dropColumn(['hero_bg_color_via', 'hero_bg_color_to']);
        });

        Schema::table('directions', function (Blueprint $table) {
            $table->renameColumn('hero_bg_color_from', 'hero_bg_color');
        });
    }
};
