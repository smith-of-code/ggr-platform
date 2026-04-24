<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tour_cabinet_documents')) {
            return;
        }

        DB::table('tour_cabinet_documents')
            ->whereIn('type', ['diploma', 'name_change_certificate'])
            ->delete();
    }

    public function down(): void
    {
        // Не восстанавливаем удалённые записи.
    }
};
