<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogSubscriberController extends Controller
{
    public function index(Request $request): Response
    {
        $query = BlogSubscriber::query()->latest();

        if ($search = $request->input('search')) {
            $query->where('email', 'like', "%{$search}%");
        }

        $subscribers = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => BlogSubscriber::count(),
            'active' => BlogSubscriber::where('is_active', true)->count(),
            'paused' => BlogSubscriber::where('is_active', false)->count(),
        ];

        return Inertia::render('Admin/Subscribers/Index', [
            'subscribers' => $subscribers,
            'stats' => $stats,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:blog_subscribers,email',
        ]);

        BlogSubscriber::create($validated);

        return redirect()->route('admin.blog-subscribers.index')->with('success', 'Подписчик добавлен');
    }

    public function destroy(BlogSubscriber $blogSubscriber): RedirectResponse
    {
        $blogSubscriber->delete();

        return redirect()->route('admin.blog-subscribers.index')->with('success', 'Подписчик удалён');
    }

    public function toggleActive(BlogSubscriber $blogSubscriber): RedirectResponse
    {
        $blogSubscriber->update(['is_active' => !$blogSubscriber->is_active]);

        return redirect()->back()->with(
            'success',
            $blogSubscriber->is_active ? 'Подписка активирована' : 'Подписка приостановлена'
        );
    }
}
