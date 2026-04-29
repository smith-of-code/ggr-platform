<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_cabinet_direction_cities', function (Blueprint $table): void {
            $table->string('lms_form_slug', 191)->nullable()->after('needs_more_data');
        });
    }

    public function down(): void
    {
        Schema::table('tour_cabinet_direction_cities', function (Blueprint $table): void {
            $table->dropColumn('lms_form_slug');
        });
    }
};
