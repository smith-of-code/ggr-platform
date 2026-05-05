<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_events', function (Blueprint $table) {
            $table->timestamp('default_assignment_deadline')->nullable()->after('menu_config');
        });
    }

    public function down(): void
    {
        Schema::table('lms_events', function (Blueprint $table) {
            $table->dropColumn('default_assignment_deadline');
        });
    }
};
