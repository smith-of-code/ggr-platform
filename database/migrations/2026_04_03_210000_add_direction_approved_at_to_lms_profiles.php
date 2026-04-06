<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->timestamp('direction_approved_at')->nullable()->after('faculty');
        });
    }

    public function down(): void
    {
        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->dropColumn('direction_approved_at');
        });
    }
};
