<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ExceptionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupActivityLogs extends Command
{
    protected $signature = 'logs:cleanup {--dry-run : Показать количество записей без удаления}';

    protected $description = 'Удалить устаревшие записи из activity_logs и exception_logs';

    public function handle(): int
    {
        $days = config('activity-logging.retention_days', 90);
        $cutoff = Carbon::now()->subDays($days);
        $dryRun = $this->option('dry-run');

        $activityCount = ActivityLog::where('created_at', '<', $cutoff)->count();
        $exceptionCount = ExceptionLog::where('created_at', '<', $cutoff)->count();

        if ($dryRun) {
            $this->info("Dry run: найдено {$activityCount} activity_logs и {$exceptionCount} exception_logs старше {$days} дней.");

            return self::SUCCESS;
        }

        $deletedActivity = ActivityLog::where('created_at', '<', $cutoff)->delete();
        $deletedExceptions = ExceptionLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Удалено: {$deletedActivity} activity_logs, {$deletedExceptions} exception_logs (старше {$days} дней).");

        return self::SUCCESS;
    }
}
