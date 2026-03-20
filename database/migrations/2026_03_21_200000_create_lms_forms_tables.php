<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_anonymous')->default(true);
            $table->boolean('allow_embed')->default(true);
            $table->boolean('create_users')->default(false);
            $table->string('fio_field_key')->nullable();
            $table->string('email_field_key')->nullable();
            $table->string('phone_field_key')->nullable();
            $table->string('position_field_key')->nullable();
            $table->text('thank_you_message')->nullable();
            $table->timestamps();
        });

        Schema::create('lms_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_form_id')->constrained('lms_forms')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->string('type', 30);
            $table->boolean('required')->default(false);
            $table->text('placeholder')->nullable();
            $table->json('options')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['lms_form_id', 'position']);
        });

        Schema::create('lms_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_form_id')->constrained('lms_forms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('user_created')->default(false);
            $table->timestamps();
        });

        Schema::create('lms_form_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_form_submission_id')->constrained('lms_form_submissions')->cascadeOnDelete();
            $table->foreignId('lms_form_field_id')->constrained('lms_form_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_form_responses');
        Schema::dropIfExists('lms_form_submissions');
        Schema::dropIfExists('lms_form_fields');
        Schema::dropIfExists('lms_forms');
    }
};
