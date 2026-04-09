<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_profile_documents', function (Blueprint $table) {
            $table->string('status', 32)->default('pending_review')->after('original_name');
            $table->text('admin_comment')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('admin_comment');
        });

        DB::table('lms_profile_documents')->where('file_path', '!=', '')->update(['status' => 'pending_review']);
    }

    public function down(): void
    {
        Schema::table('lms_profile_documents', function (Blueprint $table) {
            $table->dropColumn(['status', 'admin_comment', 'reviewed_at']);
        });
    }
};
