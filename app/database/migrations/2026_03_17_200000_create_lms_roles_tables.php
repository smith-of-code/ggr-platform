<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_event_id')->constrained('lms_events')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('lms_course_role_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('lms_role_id')->constrained('lms_roles')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['lms_course_id', 'lms_role_id']);
        });

        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->foreignId('lms_role_id')->nullable()->after('role')->constrained('lms_roles')->nullOnDelete();
            $table->string('phone')->nullable()->after('position');
        });

        Schema::table('lms_courses', function (Blueprint $table) {
            $table->timestamp('starts_at')->nullable()->after('position');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'ends_at']);
        });

        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lms_role_id');
            $table->dropColumn('phone');
        });

        Schema::dropIfExists('lms_course_role_access');
        Schema::dropIfExists('lms_roles');
    }
};
