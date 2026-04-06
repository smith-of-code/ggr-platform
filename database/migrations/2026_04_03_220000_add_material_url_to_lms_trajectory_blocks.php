<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_trajectory_blocks', function (Blueprint $table) {
            $table->string('material_url')->nullable()->after('lms_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('lms_trajectory_blocks', function (Blueprint $table) {
            $table->dropColumn('material_url');
        });
    }
};
