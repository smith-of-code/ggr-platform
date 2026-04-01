<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atoms_vkusa_content', function (Blueprint $table) {
            $table->id();

            // Hero
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_image')->nullable();

            // Этапы конкурса [{title, description}]
            $table->json('competition_stages')->nullable();

            // Условия участия [{title, description}]
            $table->json('participation_conditions')->nullable();

            // Критерии отбора [{title, description}]
            $table->json('selection_criteria')->nullable();

            // Итоги года
            $table->string('results_year')->nullable();
            $table->text('results_content')->nullable();
            $table->json('results_gallery')->nullable();   // [{url, caption}]
            $table->json('results_videos')->nullable();    // [{url, title}]
            $table->json('results_cases')->nullable();     // [{name, city, text, image}]

            // Почему это важно
            $table->text('why_important_content')->nullable();
            $table->json('why_important_stats')->nullable(); // [{value, label}]

            // Карта городов [{name, lat, lng, recipe_title, recipe_image}]
            $table->json('map_cities')->nullable();

            // Форма заявки
            $table->string('application_form_title')->nullable();
            $table->text('application_form_text')->nullable();

            // Партнёры [{name, logo, url}]
            $table->json('partners')->nullable();

            // Отзывы [{name, role, text, rating, avatar}]
            $table->json('reviews')->nullable();

            // Как конкурс помогает туризму
            $table->text('tourism_help_content')->nullable();
            $table->json('tourism_help_items')->nullable(); // [{title, description, image}]

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atoms_vkusa_content');
    }
};
