<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->json('program_days')->nullable()->after('description');
            $table->json('accommodations')->nullable()->after('accommodation_info');
            $table->text('memo_text')->nullable()->after('memo_pdf');
            $table->text('pass_info')->nullable()->after('memo_text');
        });

        Schema::create('tour_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('text')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->unique(['tour_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_reviews');

        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['program_days', 'accommodations', 'memo_text', 'pass_info']);
        });
    }
};
