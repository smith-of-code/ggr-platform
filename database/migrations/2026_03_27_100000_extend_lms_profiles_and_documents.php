<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('city')->constrained('cities')->nullOnDelete();
            $table->string('organization', 255)->nullable()->after('city_id');
            $table->text('project_description')->nullable()->after('organization');
            $table->string('preferred_channel', 20)->nullable()->after('project_description');
        });

        Schema::create('lms_profile_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_profile_id')->constrained('lms_profiles')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();

            $table->unique(['lms_profile_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_profile_documents');

        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn(['organization', 'project_description', 'preferred_channel']);
        });
    }
};
