<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->string('hero_bg_color', 20)->nullable()->after('image');
            $table->string('hero_text_color', 20)->nullable()->after('hero_bg_color');
            $table->string('hero_bg_image')->nullable()->after('hero_text_color');
            $table->boolean('hero_bg_color_enabled')->default(false)->after('hero_bg_image');
        });
    }

    public function down(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->dropColumn(['hero_bg_color', 'hero_text_color', 'hero_bg_image', 'hero_bg_color_enabled']);
        });
    }
};
