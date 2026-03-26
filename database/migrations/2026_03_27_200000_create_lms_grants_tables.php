<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('application_start')->nullable();
            $table->timestamp('application_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_grant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_grant_id')->constrained('lms_grants')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lms_grant_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_grant_id')->constrained('lms_grants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lms_grant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_grant_enrollments');
        Schema::dropIfExists('lms_grant_documents');
        Schema::dropIfExists('lms_grants');
    }
};
