<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:tour,research,program_info,atoms_vkusa',
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns',
            'phone' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:2000',
            'tour_id' => 'nullable|exists:tours,id',
            'tour_departure_id' => 'nullable|exists:tour_departures,id',
        ], [
            'email.email' => 'Введите корректный email-адрес',
        ]);

        if (!empty($validated['phone'])) {
            $validated['phone'] = self::normalizePhone($validated['phone']);
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

        return back()->with('success', 'Заявка успешно отправлена. Мы свяжемся с вами в ближайшее время.');
    }

    /**
     * Нормализация российского телефона → +7XXXXXXXXXX.
     *
     * @throws ValidationException
     */
    private static function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value);

        if (str_starts_with($digits, '8') && strlen($digits) === 11) {
            $digits = '7' . substr($digits, 1);
        }

        if (!preg_match('/^7\d{10}$/', $digits)) {
            throw ValidationException::withMessages([
                'phone' => 'Введите корректный номер телефона в формате +7 (XXX) XXX-XX-XX',
            ]);
        }

        return '+' . $digits;
    }
}
