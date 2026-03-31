<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->integer('founded_year')->nullable()->after('population');
            $table->integer('population_year')->nullable()->after('founded_year');
            $table->string('timezone', 20)->nullable()->after('population_year');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['founded_year', 'population_year', 'timezone']);
        });
    }
};
