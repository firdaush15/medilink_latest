<?php
// app/Http/Controllers/Api/ArticleApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleApiController extends Controller
{
    /**
     * GET /api/articles
     * Returns all published articles for the mobile app.
     */
    public function index(Request $request)
    {
        $query = Article::published()->ordered();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('featured')) {
            $query->featured();
        }

        $articles = $query->select([
            'article_id',
            'title',
            'source_url',
            'image_url',
            'category',
            'summary',
            'author',
            'source_name',
            'published_at',
            'is_featured',
            'view_count',
            'sort_order',
        ])->limit(50)->get();

        return response()->json([
            'success'    => true,
            'articles'   => $articles,
            'categories' => Article::categories(),
        ]);
    }

    /**
     * GET /api/articles/{id}
     * Returns a single article with full content and increments view count.
     */
    public function show(int $id)
    {
        $article = Article::published()->find($id);

        if (! $article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }

        $article->incrementViews();

        return response()->json([
            'success' => true,
            'article' => $article,
        ]);
    }
}