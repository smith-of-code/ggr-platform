<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_cabinet_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 255);
            $table->string('category', 64);
            $table->string('status', 32)->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_message_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('tour_cabinet_support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tour_cabinet_support_tickets')->cascadeOnDelete();
            $table->string('author_type', 16);
            $table->foreignId('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        Schema::create('tour_cabinet_support_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('tour_cabinet_support_messages')->cascadeOnDelete();
            $table->string('disk', 32);
            $table->string('path', 512);
            $table->string('original_filename', 255);
            $table->string('mime_type', 128);
            $table->unsignedInteger('size_bytes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_cabinet_support_attachments');
        Schema::dropIfExists('tour_cabinet_support_messages');
        Schema::dropIfExists('tour_cabinet_support_tickets');
    }
};
