<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlug;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VacancyController extends Controller
{
    use GeneratesUniqueSlug;
    public function index(): Response
    {
        $vacancies = Vacancy::with('city')
            ->orderBy('position')
            ->orderByDesc('id')
            ->paginate(20);

        return Inertia::render('Admin/Vacancies/Index', [
            'vacancies' => $vacancies,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Vacancies/Form', [
            'vacancy' => null,
            'cities' => City::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'city_id' => 'nullable|exists:cities,id',
            'company' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:50',
            'salary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'conditions' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'is_published' => 'boolean',
            'position' => 'nullable|integer',
        ]);

        $validated['slug'] = $this->uniqueSlug(Vacancy::class, $validated['title'], $validated['slug'] ?? null);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;

        Vacancy::create($validated);

        return redirect()->route('admin.vacancies.index')->with('success', 'Вакансия создана');
    }

    public function edit(Vacancy $vacancy): Response
    {
        return Inertia::render('Admin/Vacancies/Form', [
            'vacancy' => $vacancy,
            'cities' => City::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Vacancy $vacancy): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'city_id' => 'nullable|exists:cities,id',
            'company' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:50',
            'salary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'conditions' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'is_published' => 'boolean',
            'position' => 'nullable|integer',
        ]);

        $validated['slug'] = $this->uniqueSlug(Vacancy::class, $validated['title'], $validated['slug'] ?? null, $vacancy->id);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published']
            ? ($vacancy->published_at ?? now())
            : null;

        $vacancy->update($validated);

        return redirect()->route('admin.vacancies.index')->with('success', 'Вакансия обновлена');
    }

    public function destroy(Vacancy $vacancy): RedirectResponse
    {
        $vacancy->delete();
        return redirect()->route('admin.vacancies.index')->with('success', 'Вакансия удалена');
    }

    public function togglePublish(Vacancy $vacancy): RedirectResponse
    {
        $isPublished = !$vacancy->is_published;
        $vacancy->update([
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($vacancy->published_at ?? now()) : null,
        ]);

        $status = $isPublished ? 'опубликована' : 'скрыта';
        return redirect()->back()->with('success', "Вакансия «{$vacancy->title}» {$status}");
    }
}
