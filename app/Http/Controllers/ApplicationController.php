<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Consent;
use App\Services\ConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:tour,research,program_info,atoms_vkusa',
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email:rfc,strict', 'max:255'],
            'phone' => [
                'nullable',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $digits = self::phoneDigitsRu($value);
                    if ($digits === null || ! preg_match('/^7\d{10}$/', $digits)) {
                        $fail('Укажите телефон из 10 цифр после +7 или 8 (например +79161234567), либо оставьте поле пустым.');
                    }
                },
            ],
            'message' => 'nullable|string|max:2000',
            'tour_id' => 'nullable|exists:tours,id',
            'tour_departure_id' => 'nullable|exists:tour_departures,id',
            'consent' => ['accepted'],
        ], [
            'email.email' => 'Введите корректный email-адрес.',
            'consent.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
        ]);

        if (! empty($validated['phone'])) {
            $validated['phone'] = '+'.self::phoneDigitsRu($validated['phone']);
        }

        $application = Application::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'data' => ['message' => $validated['message'] ?? null],
            'tour_id' => $validated['tour_id'] ?? null,
            'tour_departure_id' => $validated['tour_departure_id'] ?? null,
            'status' => 'new',
        ]);

        ConsentService::log($request, Consent::TYPE_APPLICATION, [
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ], ['application_type' => $validated['type']]);

        return back()->with('success', 'Заявка успешно отправлена. Мы свяжемся с вами в ближайшее время.');
    }

    /** Цифры РФ: 8xxxxxxxxxx → 7xxxxxxxxxx, 9xxxxxxxxx → 7xxxxxxxxxx. */
    private static function phoneDigitsRu(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '' || strlen($digits) < 10) {
            return null;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '8')) {
            $digits = '7'.substr($digits, 1);
        }
        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            $digits = '7'.$digits;
        }

        return preg_match('/^7\d{10}$/', $digits) ? $digits : null;
    }
}
