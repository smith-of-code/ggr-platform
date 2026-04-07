<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationProduct;
use App\Models\Lms\LmsCourse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EducationProductController extends Controller
{
    public function index(): Response
    {
        $products = EducationProduct::query()
            ->orderBy('position')
            ->orderBy('id')
            ->paginate(15);

        $lmsCourses = Schema::hasTable('lms_courses')
            ? LmsCourse::query()
                ->withCount('enrollments')
                ->orderBy('position')
                ->get()
            : collect();

        return Inertia::render('Admin/EducationProducts/Index', [
            'products' => $products,
            'lmsCourses' => $lmsCourses,
        ]);
    }

    public function toggleCourseActive(LmsCourse $course): RedirectResponse
    {
        $course->update(['is_active' => !$course->is_active]);

        $status = $course->is_active ? 'опубликован' : 'скрыт';

        return redirect()->route('admin.education-products.index')
            ->with('success', "Курс «{$course->title}» {$status}");
    }

    public function create(Request $request): Response
    {
        $type = $request->query('type', EducationProduct::TYPE_EDUCATION);
        if (! in_array($type, EducationProduct::TYPES, true)) {
            $type = EducationProduct::TYPE_EDUCATION;
        }

        return Inertia::render('Admin/EducationProducts/Form', [
            'productType' => $type,
            'sectionDefinitions' => EducationProduct::SECTION_DEFINITIONS,
            'sectionLabels' => EducationProduct::SECTION_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProduct($request);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['position'] = $validated['position'] ?? 0;

        EducationProduct::create($validated);

        return redirect()->route('admin.education-products.index')->with('success', 'Продукт создан');
    }

    public function edit(EducationProduct $education_product): Response
    {
        return Inertia::render('Admin/EducationProducts/Form', [
            'product' => $education_product,
            'productType' => $education_product->type,
            'sectionDefinitions' => EducationProduct::SECTION_DEFINITIONS,
            'sectionLabels' => EducationProduct::SECTION_LABELS,
        ]);
    }

    public function update(Request $request, EducationProduct $education_product): RedirectResponse
    {
        $validated = $this->validatedProduct($request, $education_product->id);
        unset($validated['type']);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['position'] = $validated['position'] ?? 0;

        $education_product->update($validated);

        return redirect()->route('admin.education-products.index')->with('success', 'Продукт обновлён');
    }

    public function destroy(EducationProduct $education_product): RedirectResponse
    {
        $education_product->delete();

        return redirect()->route('admin.education-products.index')->with('success', 'Продукт удалён');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProduct(Request $request, ?int $exceptId = null): array
    {
        $slugRule = 'required|string|max:255|unique:education_products,slug';
        if ($exceptId !== null) {
            $slugRule .= ',' . $exceptId;
        }

        $rules = [
            'title' => 'required|string|max:255',
            'slug' => $slugRule,
            'type' => ['required', 'string', Rule::in(EducationProduct::TYPES)],
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|string|max:2048',
            'duration' => 'nullable|string|max:255',
            'format' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string',
            'price_info' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sections' => 'nullable|array',
            'sections.*.enabled' => 'boolean',
            'sections.*.content' => 'nullable|string',
            'sections.*.items' => 'nullable|array',
            'sections.*.items.*.name' => 'nullable|string|max:255',
            'sections.*.items.*.position' => 'nullable|string|max:255',
            'sections.*.items.*.photo' => 'nullable|string|max:2048',
            'sections.*.items.*.bio' => 'nullable|string',
            'regulation_file' => 'nullable|string|max:2048',
            'countries' => 'nullable|array',
            'countries.*.name' => 'required_with:countries|string|max:255',
            'countries.*.slug' => 'nullable|string|max:255',
            'countries.*.description' => 'nullable|string',
            'countries.*.content' => 'nullable|string',
        ];

        return $request->validate($rules);
    }
}
