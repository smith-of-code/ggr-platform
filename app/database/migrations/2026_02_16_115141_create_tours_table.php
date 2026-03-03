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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('start_city')->nullable(); // город старта
            $table->string('duration')->nullable(); // 2 дня, 1 ночь
            $table->string('project')->nullable(); // start_atomgrad, atoms_vkusa, llr
            $table->string('participation_type')->nullable(); // contest, paid
            $table->string('season')->nullable(); // winter, spring, summer, autumn
            $table->boolean('for_children')->default(false);
            $table->boolean('for_foreigners')->default(false);
            $table->boolean('closed_city')->default(false);
            $table->string('group_size')->nullable(); // до 30 человек
            $table->integer('min_age')->nullable();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->string('program_pdf')->nullable();
            $table->string('memo_pdf')->nullable();
            $table->text('departure_info')->nullable(); // точка сбора, логистика
            $table->text('accommodation_info')->nullable();
            $table->text('conditions')->nullable();
            $table->text('cost_info')->nullable();
            $table->text('application_info')->nullable();
            $table->boolean('bchp_participant')->default(false); // Больше чем путешествие
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->text('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
