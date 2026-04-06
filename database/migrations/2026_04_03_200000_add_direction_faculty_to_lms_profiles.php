<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->string('direction', 50)->nullable()->after('preferred_channel');
            $table->string('faculty', 50)->nullable()->after('direction');
        });
    }

    public function down(): void
    {
        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->dropColumn(['direction', 'faculty']);
        });
    }
};
