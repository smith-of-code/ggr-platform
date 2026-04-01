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
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:2000',
            'tour_id' => 'nullable|exists:tours,id',
            'tour_departure_id' => 'nullable|exists:tour_departures,id',
        ]);

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
}
