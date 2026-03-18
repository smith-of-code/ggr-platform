<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $this->settings->applyMailConfig();

            Mail::raw('Тестовое письмо от ' . config('app.name'), function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Тестовое письмо');
            });

            return redirect()->back()->with('success', 'Тестовое письмо отправлено на ' . $request->email);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Ошибка отправки: ' . $e->getMessage());
        }
    }
}
