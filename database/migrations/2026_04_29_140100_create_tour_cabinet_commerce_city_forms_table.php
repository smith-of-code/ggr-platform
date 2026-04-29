<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_cabinet_commerce_city_forms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('lms_form_slug', 191);
            $table->timestamps();

            $table->unique('city_id');
            $table->index('lms_form_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_cabinet_commerce_city_forms');
    }
};
