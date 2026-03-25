<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('favorable_type');
            $table->unsignedBigInteger('favorable_id');
            $table->timestamps();

            $table->unique(['user_id', 'favorable_type', 'favorable_id']);
        });

        Schema::create('tour_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->string('emoji', 10);
            $table->timestamps();

            $table->unique(['tour_id', 'user_id']);
            $table->index(['tour_id', 'ip_address']);
        });

        Schema::create('education_products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('duration')->nullable();
            $table->string('format')->nullable();
            $table->text('target_audience')->nullable();
            $table->string('price_info')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('researches', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('year')->nullable();
            $table->text('methodology')->nullable();
            $table->text('results_summary')->nullable();
            $table->string('pdf_file')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('cooking_time')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('servings')->nullable();
            $table->json('ingredients')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->string('link')->nullable();
            $table->enum('type', ['news', 'event', 'milestone'])->default('event');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->string('source')->nullable();
            $table->enum('status', ['new', 'read', 'replied'])->default('new');
            $table->timestamps();
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->string('region')->nullable();
            $table->integer('population')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->json('attractions')->nullable();
            $table->json('social_objects')->nullable();
            $table->json('gallery')->nullable();
            $table->string('video_url')->nullable();
            $table->json('facts')->nullable();
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->text('target_audience')->nullable();
            $table->text('organizer_info')->nullable();
            $table->json('reactions_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'target_audience',
                'organizer_info',
                'reactions_count',
            ]);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn([
                'region',
                'population',
                'lat',
                'lng',
                'attractions',
                'social_objects',
                'gallery',
                'video_url',
                'facts',
            ]);
        });

        Schema::dropIfExists('contact_submissions');
        Schema::dropIfExists('timeline_events');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('researches');
        Schema::dropIfExists('education_products');
        Schema::dropIfExists('tour_reactions');
        Schema::dropIfExists('favorites');
    }
};
