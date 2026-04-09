<?php

namespace App\Http\Controllers;

use App\Models\EducationProduct;
use App\Models\Post;
use App\Services\SettingsService;
use App\Support\VshgrPageContent;
use Inertia\Inertia;
use Inertia\Response;

class EducationController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function index(): Response
    {
        $products = EducationProduct::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('title')
            ->get();

        $latestAnnouncements = Post::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('category', 'announcements')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $raw = $this->settings->getGroup(VshgrPageContent::GROUP);
        $pageData = VshgrPageContent::mergeFromStored($raw);

        return Inertia::render('Education/Index', [
            'products' => $products,
            'latestAnnouncements' => $latestAnnouncements,
            'pageData' => $pageData,
        ]);
    }

    public function show(string $slug): Response
    {
        $product = EducationProduct::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Education/Show', [
            'product' => $product,
        ]);
    }
}
