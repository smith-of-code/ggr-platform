<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewPostNotifications;
use App\Models\Post;
use Carbon\Carbon;
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
            'videos' => 'nullable|array',
            'videos.*' => 'nullable|url|max:2048',
            'is_published' => 'boolean',
            'published_at' => ['nullable', 'string', 'max:40'],
        ]);

        $isPublished = $request->boolean('is_published');
        $validated['is_published'] = $isPublished;
        $validated['published_at'] = $this->resolvePublishedAt($isPublished, $request->input('published_at'), null);

        $post = Post::create($validated);

        if ($post->is_published) {
            SendNewPostNotifications::dispatch($post);
        }

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
            'videos' => 'nullable|array',
            'videos.*' => 'nullable|url|max:2048',
            'is_published' => 'boolean',
            'published_at' => ['nullable', 'string', 'max:40'],
        ]);

        $isPublished = $request->boolean('is_published');
        $validated['is_published'] = $isPublished;
        $validated['published_at'] = $this->resolvePublishedAt(
            $isPublished,
            $request->input('published_at'),
            $post->published_at
        );
        $validated['videos'] = array_values(array_filter($validated['videos'] ?? []));

        $wasPublished = $post->is_published;
        $post->update($validated);

        if (!$wasPublished && $post->is_published) {
            SendNewPostNotifications::dispatch($post);
        }

        return redirect()->route('admin.blog.index')->with('success', 'Запись обновлена');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Запись удалена');
    }

    public function togglePublish(Post $post): RedirectResponse
    {
        $wasPublished = $post->is_published;
        $isPublished = !$wasPublished;
        $post->update([
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($post->published_at ?? now()) : null,
        ]);

        if (!$wasPublished && $isPublished) {
            SendNewPostNotifications::dispatch($post);
        }

        return redirect()->back()->with(
            'success',
            $isPublished ? 'Запись опубликована' : 'Запись снята с публикации'
        );
    }

    /**
     * @param  \Carbon\Carbon|string|null  $fallbackWhenEmpty  при опубликованной записи и пустом поле — прежняя дата или now()
     */
    private function resolvePublishedAt(bool $isPublished, mixed $input, mixed $fallbackWhenEmpty): ?Carbon
    {
        if (! $isPublished) {
            return null;
        }

        $trimmed = is_string($input) ? trim($input) : '';
        if ($trimmed !== '') {
            return Carbon::parse($trimmed);
        }

        return $fallbackWhenEmpty instanceof Carbon
            ? $fallbackWhenEmpty
            : ($fallbackWhenEmpty ? Carbon::parse($fallbackWhenEmpty) : now());
    }
}
