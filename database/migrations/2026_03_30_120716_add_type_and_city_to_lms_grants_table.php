<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_grants', function (Blueprint $table) {
            $table->string('type')->default('grant')->after('title');
            $table->string('city')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('lms_grants', function (Blueprint $table) {
            $table->dropColumn(['type', 'city']);
        });
    }
};
