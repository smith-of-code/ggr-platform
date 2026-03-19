<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->string('first_name')->nullable()->after('last_name');
        });

        // Разбиваем существующее поле name на last_name и first_name
        DB::statement("
            UPDATE users SET
                last_name  = split_part(name, ' ', 1),
                first_name = CASE
                    WHEN array_length(string_to_array(name, ' '), 1) >= 2
                    THEN split_part(name, ' ', 2)
                    ELSE NULL
                END
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'first_name']);
        });
    }
};
