<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tag' => ['nullable', 'string', 'max:50'],
            'featured' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = DB::table('web_news')
            ->where('is_published', true)
            ->where('published_at', '<=', now());

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        $perPage = min($request->input('per_page', 12), 50);
        $news = $query->orderBy('published_at', 'desc')
                     ->paginate($perPage);

        return response()->json([
            'data' => collect($news->items())->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'image' => $article->image,
                    'is_featured' => (bool) $article->is_featured,
                    'published_at' => $article->published_at,
                    'tags' => json_decode($article->tags, true) ?? [],
                ];
            }),
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
                'from' => $news->firstItem(),
                'to' => $news->lastItem(),
            ],
            'links' => [
                'first' => $news->url(1),
                'last' => $news->url($news->lastPage()),
                'prev' => $news->previousPageUrl(),
                'next' => $news->nextPageUrl(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $article = DB::table('web_news')
            ->where('id', $id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->first();

        if (!$article) {
            return response()->json([
                'message' => 'Article not found'
            ], 404);
        }

        $author = DB::table('web_users')
            ->where('id', $article->author_id)
            ->select('id', 'name')
            ->first();

        $data = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'image' => $article->image,
            'is_featured' => (bool) $article->is_featured,
            'published_at' => $article->published_at,
            'tags' => json_decode($article->tags, true) ?? [],
            'author' => $author ? [
                'id' => $author->id,
                'name' => $author->name,
            ] : null,
        ];

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create news');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $validated['slug'] = \Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();
        $validated['tags'] = json_encode($validated['tags'] ?? []);
        
        if ($validated['is_published'] && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $articleId = DB::table('web_news')->insertGetId($validated + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $article = DB::table('web_news')->where('id', $articleId)->first();

        return response()->json([
            'message' => 'Article created successfully',
            'data' => $article
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $article = DB::table('web_news')->where('id', $id)->first();
        
        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        $this->authorize('edit news');

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'excerpt' => ['sometimes', 'nullable', 'string', 'max:500'],
            'content' => ['sometimes', 'string'],
            'image' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = \Str::slug($validated['title']);
        }

        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        $validated['updated_at'] = now();

        DB::table('web_news')->where('id', $id)->update($validated);

        $updatedArticle = DB::table('web_news')->where('id', $id)->first();

        return response()->json([
            'message' => 'Article updated successfully',
            'data' => $updatedArticle
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $article = DB::table('web_news')->where('id', $id)->first();
        
        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        $this->authorize('delete news');

        DB::table('web_news')->where('id', $id)->delete();

        return response()->json([
            'message' => 'Article deleted successfully'
        ]);
    }

    public function featured(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $limit = min($request->input('limit', 5), 10);

        $articles = DB::table('web_news')
            ->where('is_published', true)
            ->where('is_featured', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

        $data = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'image' => $article->image,
                'published_at' => $article->published_at,
                'tags' => json_decode($article->tags, true) ?? [],
            ];
        });

        return response()->json(['data' => $data]);
    }
}