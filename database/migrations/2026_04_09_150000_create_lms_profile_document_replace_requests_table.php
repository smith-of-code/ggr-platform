<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_profile_document_replace_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_profile_id')->constrained('lms_profiles')->cascadeOnDelete();
            $table->string('type', 50);
            $table->text('user_comment');
            $table->string('status', 20)->default('pending');
            $table->text('admin_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['lms_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_profile_document_replace_requests');
    }
};
