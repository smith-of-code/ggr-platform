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

        $posts = $query->paginate(12)->withQueryString();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'categories' => Post::CATEGORIES,
            'filters' => [
                'category' => is_string($category) && array_key_exists($category, Post::CATEGORIES) ? $category : null,
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
