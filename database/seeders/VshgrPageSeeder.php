<?php

namespace Database\Seeders;

use App\Support\VshgrPageContent;
use Illuminate\Database\Seeder;

/**
 * Только настройки лендинга /vshgr (группа settings vshgr_page).
 *
 * Запуск: php artisan db:seed --class=VshgrPageSeeder
 */
class VshgrPageSeeder extends Seeder
{
    public function run(): void
    {
        VshgrPageContent::seedDefaultsIntoDatabase();
    }
}
