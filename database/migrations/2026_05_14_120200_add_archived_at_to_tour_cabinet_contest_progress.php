<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_cabinet_contest_progress', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('completion_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('tour_cabinet_contest_progress', function (Blueprint $table): void {
            $table->dropColumn('archived_at');
        });
    }
};
