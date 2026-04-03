<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index');
    }

    public function mail(): Response
    {
        return Inertia::render('Admin/Settings/Mail', [
            'settings' => $this->settings->getMailSettings(),
        ]);
    }

    public function updateMail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mailer' => 'required|string|in:smtp,sendmail,log',
            'host' => 'required_if:mailer,smtp|nullable|string|max:255',
            'port' => 'required_if:mailer,smtp|nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|string|in:tls,ssl',
            'from_address' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
        ]);

        $this->settings->setGroup('mail', $validated);

        return redirect()->back()->with('success', 'Настройки почты сохранены');
    }

    public function testMail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'count' => 'required|integer|min:1|max:50',
        ]);

        $appName = config('app.name');
        $count = (int) $validated['count'];

        for ($i = 1; $i <= $count; $i++) {
            $label = $count > 1 ? " #{$i}/{$count}" : '';

            SendMailJob::dispatch(
                to: $validated['email'],
                rawBody: "Тестовое письмо{$label} от {$appName}",
                subject: "Тестовое письмо{$label}",
            );
        }

        $word = $this->pluralizeEmails($count);

        return redirect()->back()->with(
            'success',
            "{$count} {$word} добавлено в очередь на отправку ({$validated['email']})"
        );
    }

    public function testMailDirect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $appName = config('app.name');
        $now = now()->format('d.m.Y H:i:s');

        try {
            Mail::raw(
                "Прямое тестовое письмо от {$appName}\nОтправлено: {$now}\n\nЕсли вы видите это письмо — SMTP настроен корректно.",
                function ($message) use ($validated, $appName) {
                    $message->to($validated['email'])
                        ->subject("[{$appName}] Прямой тест SMTP");
                }
            );
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Ошибка SMTP: '.$e->getMessage());
        }

        return redirect()->back()->with('success', "Письмо успешно отправлено напрямую на {$validated['email']}");
    }

    private function pluralizeEmails(int $count): string
    {
        $mod10 = $count % 10;
        $mod100 = $count % 100;

        if ($mod100 >= 11 && $mod100 <= 19) {
            return 'писем';
        }

        return match ($mod10) {
            1 => 'письмо',
            2, 3, 4 => 'письма',
            default => 'писем',
        };
    }
}
