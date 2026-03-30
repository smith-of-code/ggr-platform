<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Post::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at');

        $category = $request->query('category');
        if (is_string($category) && array_key_exists($category, Post::CATEGORIES)) {
            $query->where('category', $category);
        }

        $search = $request->query('search');
        if (is_string($search) && mb_strlen(trim($search)) >= 2) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('excerpt', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $tag = $request->query('tag');
        if (is_string($tag) && mb_strlen(trim($tag)) >= 1) {
            $query->whereJsonContains('tags', trim($tag));
        }

        $posts = $query->paginate(12)->withQueryString();

        $allTags = Post::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'categories' => Post::CATEGORIES,
            'allTags' => $allTags,
            'filters' => [
                'category' => is_string($category) && array_key_exists($category, Post::CATEGORIES) ? $category : null,
                'search' => is_string($search) && mb_strlen(trim($search)) >= 2 ? trim($search) : null,
                'tag' => is_string($tag) && mb_strlen(trim($tag)) >= 1 ? trim($tag) : null,
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->firstOrFail();

        $relatedPosts = Post::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('category', $post->category)
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
