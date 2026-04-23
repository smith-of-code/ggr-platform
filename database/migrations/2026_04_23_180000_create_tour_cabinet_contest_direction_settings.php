<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_cabinet_contest_direction_settings', function (Blueprint $table) {
            $table->id();
            $table->string('project_key', 32)->unique();
            $table->unsignedTinyInteger('max_contest_stages')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_cabinet_contest_direction_settings');
    }
};
