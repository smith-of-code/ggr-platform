<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->dropColumn('project_key');
        });
    }

    public function down(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->string('project_key', 50)->nullable()->after('image');
        });
    }
};
