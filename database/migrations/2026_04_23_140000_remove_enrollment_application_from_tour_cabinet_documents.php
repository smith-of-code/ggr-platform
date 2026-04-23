<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tour_cabinet_documents')) {
            return;
        }

        $rows = DB::table('tour_cabinet_documents')->where('type', 'enrollment_application')->get();
        $disk = config('filesystems.upload_disk', 'public');

        foreach ($rows as $row) {
            if (is_string($row->file_path ?? null) && $row->file_path !== '') {
                Storage::disk($disk)->delete($row->file_path);
            }
        }

        DB::table('tour_cabinet_documents')->where('type', 'enrollment_application')->delete();
    }

    public function down(): void
    {
        //
    }
};
