<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return Inertia::render('Admin/EducationProducts/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/EducationProducts/Form');
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
        ]);
    }

    public function update(Request $request, EducationProduct $education_product): RedirectResponse
    {
        $validated = $this->validatedProduct($request, $education_product->id);

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

        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => $slugRule,
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|string|max:2048',
            'duration' => 'nullable|string|max:255',
            'format' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string',
            'price_info' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
    }
}
