<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->string('status', 20)->default('imported')->after('lms_role_id');
            $table->string('invite_token', 64)->nullable()->unique()->after('status');
            $table->timestamp('invited_at')->nullable()->after('invite_token');
            $table->timestamp('activated_at')->nullable()->after('invited_at');
        });
    }

    public function down(): void
    {
        Schema::table('lms_profiles', function (Blueprint $table) {
            $table->dropColumn(['status', 'invite_token', 'invited_at', 'activated_at']);
        });
    }
};
