<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('label')->nullable();
            $table->foreignId('lms_role_id')->nullable()->constrained('lms_roles')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('uses_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['lms_event_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_invitations');
    }
};
