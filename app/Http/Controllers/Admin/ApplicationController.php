<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Application::with(['tour', 'tourDeparture'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $applications,
            'filters' => $request->only('status', 'type', 'search'),
            'statusCounts' => [
                'all' => Application::count(),
                'new' => Application::where('status', 'new')->count(),
                'in_progress' => Application::where('status', 'in_progress')->count(),
                'approved' => Application::where('status', 'approved')->count(),
                'rejected' => Application::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function show(Application $application): Response
    {
        $application->load(['tour', 'tourDeparture']);

        return Inertia::render('Admin/Applications/Show', [
            'application' => $application,
        ]);
    }

    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:new,in_progress,approved,rejected',
        ]);

        $application->update($validated);

        return redirect()->back()->with('success', 'Статус обновлён');
    }

    public function export(): StreamedResponse
    {
        $applications = Application::with(['tour', 'tourDeparture'])->latest()->get();

        $filename = 'applications_' . date('Y-m-d_His') . '.csv';

        return ResponseFacade::streamDownload(function () use ($applications) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Тип', 'Имя', 'Email', 'Телефон', 'Тур', 'Дата заезда', 'Статус', 'Дата создания'], ';');
            foreach ($applications as $app) {
                fputcsv($handle, [
                    $app->id,
                    $app->type,
                    $app->name,
                    $app->email,
                    $app->phone ?? '',
                    $app->tour?->title ?? '',
                    $app->tourDeparture ? $app->tourDeparture->start_date->format('d.m.Y') : '',
                    $app->status,
                    $app->created_at->format('d.m.Y H:i'),
                ], ';');
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
