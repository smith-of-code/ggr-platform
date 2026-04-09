<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE lms_grants
            ALTER COLUMN city TYPE jsonb
            USING CASE
                WHEN city IS NOT NULL AND city != '' THEN jsonb_build_array(city)
                ELSE NULL
            END
        ");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE lms_grants
            ALTER COLUMN city TYPE varchar(255)
            USING CASE
                WHEN city IS NOT NULL THEN city->>0
                ELSE NULL
            END
        ");
    }
};
