<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_forms', function (Blueprint $table) {
            $table->string('consent_document_url', 500)->nullable()->after('require_consent');
        });
    }

    public function down(): void
    {
        Schema::table('lms_forms', function (Blueprint $table) {
            $table->dropColumn('consent_document_url');
        });
    }
};
