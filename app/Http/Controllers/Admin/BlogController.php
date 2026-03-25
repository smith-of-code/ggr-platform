<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $posts = Post::query()
            ->orderByDesc('published_at')
            ->paginate(15);

        return Inertia::render('Admin/Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Blog/Form', [
            'categories' => Post::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|string|max:2048',
            'category' => ['required', Rule::in(array_keys(Post::CATEGORIES))],
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'is_published' => 'boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;

        Post::create($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Запись создана');
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('Admin/Blog/Form', [
            'post' => $post,
            'categories' => Post::CATEGORIES,
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug,' . $post->id,
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|string|max:2048',
            'category' => ['required', Rule::in(array_keys(Post::CATEGORIES))],
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'is_published' => 'boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published']
            ? ($post->published_at ?? now())
            : null;

        $post->update($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Запись обновлена');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Запись удалена');
    }

    public function togglePublish(Post $post): RedirectResponse
    {
        $isPublished = !$post->is_published;
        $post->update([
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($post->published_at ?? now()) : null,
        ]);

        return redirect()->back()->with(
            'success',
            $isPublished ? 'Запись опубликована' : 'Запись снята с публикации'
        );
    }
}
