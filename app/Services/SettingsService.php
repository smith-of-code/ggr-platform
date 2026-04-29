<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingsService
{
    private const CACHE_TTL = 3600;

    /** Настройки ЛК туров (переопределение env/config). */
    private const GROUP_TOUR_CABINET = 'tour_cabinet';

    public function getMailSettings(): array
    {
        $defaults = [
            'mailer' => env('MAIL_MAILER', 'smtp'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION'),
            'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'from_name' => env('MAIL_FROM_NAME', config('app.name')),
        ];

        $dbSettings = $this->getGroup('mail');

        return array_merge($defaults, array_filter($dbSettings, fn ($v) => $v !== null && $v !== ''));
    }

    public function getGroup(string $group): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        return Cache::remember(
            "settings.{$group}",
            self::CACHE_TTL,
            fn () => Setting::getGroup($group),
        );
    }

    public function getGroupFresh(string $group): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        return Setting::getGroup($group);
    }

    public function setGroup(string $group, array $values): void
    {
        Setting::setGroup($group, $values);
        Cache::forget("settings.{$group}");
    }

    public function getHiddenPages(): array
    {
        $settings = $this->getGroup('page_visibility');

        $hidden = [];
        foreach ($settings as $slug => $value) {
            $decoded = is_string($value) ? json_decode($value, true) : $value;
            if (is_array($decoded) && ! empty($decoded['hidden'])) {
                $hidden[] = $slug;
            }
        }

        return $hidden;
    }

    public function setPageVisibility(array $visibility): void
    {
        $values = [];
        foreach ($visibility as $slug => $hidden) {
            $values[$slug] = json_encode(['hidden' => (bool) $hidden]);
        }

        $this->setGroup('page_visibility', $values);
    }

    /**
     * Slug формы этапа 1 (стандартная анкета): сначала БД (группа tour_cabinet), иначе config/tour_cabinet.php.
     */
    public function getTourCabinetContestStage1FormSlugStandard(): ?string
    {
        return $this->resolveTourCabinetContestFormSlug('contest_stage1_form_slug_standard');
    }

    /**
     * Slug формы этапа 1 («нужно больше данных»): сначала БД, иначе config.
     */
    public function getTourCabinetContestStage1FormSlugMoreData(): ?string
    {
        return $this->resolveTourCabinetContestFormSlug('contest_stage1_form_slug_more_data');
    }

    /**
     * Slug отдельной «стандартной анкеты» дашборда ЛК туров: сначала БД (группа tour_cabinet),
     * иначе config/tour_cabinet.php (`dashboard_standard_form_slug`).
     * Любая LmsForm платформы; не обязан совпадать с slug этапа 1.
     */
    public function getTourCabinetDashboardStandardFormSlug(): ?string
    {
        return $this->resolveTourCabinetContestFormSlug('dashboard_standard_form_slug');
    }

    /**
     * Настройка письма «Конкурс пройден» (этап 3 завершён): приоритет — БД (группа tour_cabinet),
     * иначе config/tour_cabinet.php / env. Поля: enabled (bool), subject (string), body (string).
     *
     * @return array{enabled: bool, subject: string, body: string}
     */
    public function getTourCabinetContestCompletionNotification(): array
    {
        $db = $this->getGroup(self::GROUP_TOUR_CABINET);

        $cfg = (array) config('tour_cabinet.contest_completion_notification', []);
        $defaultSubject = is_string($cfg['subject'] ?? null) ? (string) $cfg['subject'] : '';
        $defaultBody = is_string($cfg['body'] ?? null) ? (string) $cfg['body'] : '';
        $defaultEnabled = (bool) ($cfg['enabled'] ?? false);

        $rawEnabled = $db['contest_completion_notification_enabled'] ?? null;
        $enabled = $rawEnabled === null
            ? $defaultEnabled
            : filter_var($rawEnabled, FILTER_VALIDATE_BOOLEAN);

        $subject = $this->resolveTourCabinetCompletionString(
            $db['contest_completion_notification_subject'] ?? null,
            $defaultSubject
        );

        $body = $this->resolveTourCabinetCompletionString(
            $db['contest_completion_notification_body'] ?? null,
            $defaultBody
        );

        return [
            'enabled' => $enabled,
            'subject' => $subject,
            'body' => $body,
        ];
    }

    private function resolveTourCabinetCompletionString(mixed $raw, string $default): string
    {
        if (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed !== '') {
                return $raw;
            }
        }

        return $default;
    }

    /**
     * Общие сроки этапов конкурса в ЛК туров (даты Y-m-d или null).
     *
     * @return array<int, array{start: ?string, end: ?string}>
     */
    public function getTourCabinetContestStageDeadlines(): array
    {
        $db = $this->getGroup(self::GROUP_TOUR_CABINET);
        $out = [];
        for ($i = 1; $i <= 3; $i++) {
            $out[$i] = [
                'start' => $this->normalizeTourCabinetDeadline($db["contest_stage_{$i}_deadline_start"] ?? null),
                'end' => $this->normalizeTourCabinetDeadline($db["contest_stage_{$i}_deadline_end"] ?? null),
            ];
        }

        return $out;
    }

    private function normalizeTourCabinetDeadline(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $t = trim($value);
        if ($t === '') {
            return null;
        }
        try {
            return Carbon::parse($t)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveTourCabinetContestFormSlug(string $key): ?string
    {
        $db = $this->getGroup(self::GROUP_TOUR_CABINET);
        $raw = $db[$key] ?? null;
        if (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        $cfg = config('tour_cabinet.'.$key);
        if (is_string($cfg)) {
            $trimmed = trim($cfg);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    public function applyMailConfig(): void
    {
        if (! $this->tableExists()) {
            return;
        }

        $settings = $this->getMailSettings();

        $scheme = match ($settings['encryption'] ?? null) {
            'ssl' => 'smtps',
            'tls' => null,
            default => null,
        };

        config([
            'mail.default' => $settings['mailer'],
            'mail.mailers.smtp.host' => $settings['host'],
            'mail.mailers.smtp.port' => (int) $settings['port'],
            'mail.mailers.smtp.username' => $settings['username'],
            'mail.mailers.smtp.password' => $settings['password'],
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.from.address' => $settings['from_address'],
            'mail.from.name' => $settings['from_name'],
        ]);
    }

    private function tableExists(): bool
    {
        return Cache::remember('settings.table_exists', 60, function () {
            try {
                return Schema::hasTable('settings');
            } catch (\Throwable) {
                return false;
            }
        });
    }
}
