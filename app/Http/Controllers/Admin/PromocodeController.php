<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PromocodeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Promocode::query()->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/Promocodes/Index', [
            'promocodes' => $query->paginate(15)->withQueryString(),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Promocodes/Form', [
            'tours' => Tour::query()->where('is_active', true)->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePromocode($request);
        $validated['code'] = $this->resolveCode($request);
        $this->resolveMorph($validated);

        Promocode::create($validated);

        return redirect()->route('admin.promocodes.index')->with('success', 'Промокод создан');
    }

    public function edit(Promocode $promocode): Response
    {
        return Inertia::render('Admin/Promocodes/Form', [
            'promocode' => $promocode,
            'tours' => Tour::query()->where('is_active', true)->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Promocode $promocode): RedirectResponse
    {
        $validated = $this->validatePromocode($request, $promocode->id);
        $validated['code'] = $this->resolveCode($request, $promocode);
        $this->resolveMorph($validated);

        $promocode->update($validated);

        return redirect()->route('admin.promocodes.index')->with('success', 'Промокод обновлён');
    }

    public function destroy(Promocode $promocode): RedirectResponse
    {
        $promocode->delete();

        return redirect()->route('admin.promocodes.index')->with('success', 'Промокод удалён');
    }

    public function toggleActive(Promocode $promocode): RedirectResponse
    {
        $promocode->update(['is_active' => ! $promocode->is_active]);

        return redirect()->back()->with('success', $promocode->is_active ? 'Промокод активирован' : 'Промокод деактивирован');
    }

    public function generateCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['name' => 'nullable|string|max:255']);

        $name = $request->input('name');

        if ($name) {
            $code = Str::upper(Str::slug($name, ''));
        } else {
            $code = Str::upper(Str::random(8));
        }

        return response()->json(['code' => $code]);
    }

    private function validatePromocode(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('promocodes', 'code')->ignore($ignoreId),
            ],
            'discount_percent' => 'required|integer|min:1|max:100',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'tour_id' => 'nullable|exists:tours,id',
            'is_active' => 'boolean',
        ], [
            'code.regex' => 'Код может содержать только латинские буквы, цифры, дефис и подчёркивание.',
            'code.unique' => 'Промокод с таким кодом уже существует.',
        ]);
    }

    private function resolveCode(Request $request, ?Promocode $existing = null): string
    {
        return Str::upper($request->input('code'));
    }

    private function resolveMorph(array &$validated): void
    {
        if (! empty($validated['tour_id'])) {
            $validated['promocodeable_type'] = Tour::class;
            $validated['promocodeable_id'] = $validated['tour_id'];
        } else {
            $validated['promocodeable_type'] = null;
            $validated['promocodeable_id'] = null;
        }
        unset($validated['tour_id']);
    }
}
