<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingsService
{
    private const CACHE_TTL = 3600;

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

    public function setGroup(string $group, array $values): void
    {
        Setting::setGroup($group, $values);
        Cache::forget("settings.{$group}");
    }

    public function applyMailConfig(): void
    {
        if (! $this->tableExists()) {
            return;
        }

        $settings = $this->getMailSettings();

        config([
            'mail.default' => $settings['mailer'],
            'mail.mailers.smtp.host' => $settings['host'],
            'mail.mailers.smtp.port' => (int) $settings['port'],
            'mail.mailers.smtp.username' => $settings['username'],
            'mail.mailers.smtp.password' => $settings['password'],
            'mail.mailers.smtp.encryption' => $settings['encryption'],
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
