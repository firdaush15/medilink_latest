<?php
// app/Http/Controllers/Admin/AdminArticleController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminArticleController extends Controller
{
    // ----------------------------------------
    // INDEX
    // ----------------------------------------
    public function index(Request $request)
    {
        $query = Article::withTrashed()->with('createdBy')->ordered();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('source_name', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'published'   => $query->whereNull('deleted_at')->where('is_published', true),
                'unpublished' => $query->whereNull('deleted_at')->where('is_published', false),
                'deleted'     => $query->whereNotNull('deleted_at'),
                default       => null,
            };
        }

        $articles   = $query->paginate(15)->withQueryString();
        $categories = Article::categories();
        $stats = [
            'total'       => Article::withTrashed()->count(),
            'published'   => Article::published()->count(),
            'unpublished' => Article::where('is_published', false)->count(),
            'featured'    => Article::featured()->count(),
        ];

        return view('admin.admin_articleManagement', compact('articles', 'categories', 'stats'));
    }

    // ----------------------------------------
    // CREATE
    // ----------------------------------------
    public function create()
    {
        $categories = Article::categories();
        return view('admin.admin_articleManagementCreate', compact('categories'));
    }

    // ----------------------------------------
    // FETCH URL — scrape metadata
    // ✅ strips #fragment, handles relative image URLs
    // ----------------------------------------
    public function fetchUrl(Request $request)
    {
        $request->validate(['url' => 'required|string|max:1000']);

        $rawUrl   = $request->url;
        $fetchUrl = strtok($rawUrl, '#');

        if (! filter_var($fetchUrl, FILTER_VALIDATE_URL)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid URL (http:// or https://).',
            ]);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; MediLinkBot/1.0)',
                'Accept'     => 'text/html,application/xhtml+xml',
            ])->timeout(12)->get($fetchUrl);

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => "The website returned status {$response->status()}. Try a different URL.",
                ]);
            }

            $html = $response->body();

            // ── Title ──────────────────────────────────────────
            $title = '';
            if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) {
                $title = html_entity_decode($m[1], ENT_QUOTES);
            }
            if (empty($title) && preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                $title = html_entity_decode(strip_tags($m[1]), ENT_QUOTES);
            }

            // ── Description ────────────────────────────────────
            $summary = '';
            if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) {
                $summary = html_entity_decode($m[1], ENT_QUOTES);
            } elseif (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) {
                $summary = html_entity_decode($m[1], ENT_QUOTES);
            }

            // ── Cover image ─────────────────────────────────────
            $imageUrl = '';
            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) {
                $imageUrl = trim($m[1]);
            }

            // ✅ Fix relative image URLs (e.g. //cdn.example.com/img.jpg or /img.jpg)
            if ($imageUrl && ! str_starts_with($imageUrl, 'http')) {
                $scheme = parse_url($fetchUrl, PHP_URL_SCHEME) ?? 'https';
                $host   = parse_url($fetchUrl, PHP_URL_HOST)   ?? '';
                if (str_starts_with($imageUrl, '//')) {
                    $imageUrl = $scheme . ':' . $imageUrl;
                } elseif (str_starts_with($imageUrl, '/')) {
                    $imageUrl = $scheme . '://' . $host . $imageUrl;
                }
            }

            // ── Source name from domain ─────────────────────────
            $host       = parse_url($fetchUrl, PHP_URL_HOST);
            $sourceName = $host ? preg_replace('/^www\./', '', $host) : '';

            // ── Body text extraction ────────────────────────────
            $clean = preg_replace('/<(script|style|noscript|header|footer|nav|aside)[^>]*>.*?<\/\1>/is', '', $html);
            preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $clean, $pMatches);
            $paragraphs = array_filter(array_map(function ($p) {
                $text = trim(strip_tags($p));
                return strlen($text) > 60 ? $text : null;
            }, $pMatches[1] ?? []));
            $content = implode("\n\n", array_slice($paragraphs, 0, 40));

            // ── Author ──────────────────────────────────────────
            $author = '';
            if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) {
                $author = html_entity_decode($m[1], ENT_QUOTES);
            }

            return response()->json([
                'success'     => true,
                'title'       => trim($title),
                'summary'     => Str::limit(trim($summary), 300),
                'image_url'   => $imageUrl,
                'source_name' => $sourceName,
                'author'      => trim($author),
                'content'     => trim($content),
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not connect to that website. Please check the URL or fill in the details manually.',
            ]);
        } catch (\Exception $e) {
            Log::error('Article URL fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching URL. Please fill in the fields manually.',
            ]);
        }
    }

    // ----------------------------------------
    // STORE
    // ✅ FIX: removed default `true` from boolean('is_published')
    //         so unchecking the toggle correctly saves as unpublished
    // ----------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'source_url'   => 'nullable|string|max:1000',
            'image_url'    => 'nullable|string|max:1000',
            'category'     => 'required|string|max:100',
            'summary'      => 'nullable|string|max:500',
            'content'      => 'nullable|string',
            'author'       => 'nullable|string|max:150',
            'source_name'  => 'nullable|string|max:150',
            'published_at' => 'nullable|date',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        $validated['created_by']   = auth()->id();
        // ✅ No default — absent checkbox = false (unpublished)
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_featured']  = $request->boolean('is_featured');
        $validated['sort_order']   = $validated['sort_order'] ?? 0;

        Article::create($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article "' . $validated['title'] . '" created successfully.');
    }

    // ----------------------------------------
    // SHOW
    // ----------------------------------------
    public function show(int $id)
    {
        $article    = Article::withTrashed()->findOrFail($id);
        $categories = Article::categories();
        return view('admin.admin_articleManagementShow', compact('article', 'categories'));
    }

    // ----------------------------------------
    // EDIT
    // ----------------------------------------
    public function edit(int $id)
    {
        $article    = Article::findOrFail($id);
        $categories = Article::categories();
        return view('admin.admin_articleManagementEdit', compact('article', 'categories'));
    }

    // ----------------------------------------
    // UPDATE
    // ----------------------------------------
    public function update(Request $request, int $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'source_url'   => 'nullable|string|max:1000',
            'image_url'    => 'nullable|string|max:1000',
            'category'     => 'required|string|max:100',
            'summary'      => 'nullable|string|max:500',
            'content'      => 'nullable|string',
            'author'       => 'nullable|string|max:150',
            'source_name'  => 'nullable|string|max:150',
            'published_at' => 'nullable|date',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_featured']  = $request->boolean('is_featured');
        $validated['sort_order']   = $validated['sort_order'] ?? 0;

        $article->update($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    // ----------------------------------------
    // UPDATE SORT ORDER (AJAX only — dedicated endpoint)
    // ✅ NEW: avoids sending full form data just to update order
    // ----------------------------------------
    public function updateSortOrder(Request $request, int $id)
    {
        $request->validate(['sort_order' => 'required|integer|min:0']);
        Article::findOrFail($id)->update(['sort_order' => $request->sort_order]);
        return response()->json(['success' => true]);
    }

    // ----------------------------------------
    // DESTROY
    // ----------------------------------------
    public function destroy(int $id)
    {
        Article::findOrFail($id)->delete();
        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted.');
    }

    // ----------------------------------------
    // RESTORE
    // ----------------------------------------
    public function restore(int $id)
    {
        Article::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.articles.index')
            ->with('success', 'Article restored.');
    }

    // ----------------------------------------
    // TOGGLE PUBLISH (AJAX)
    // ----------------------------------------
    public function togglePublish(int $id)
    {
        $article = Article::findOrFail($id);
        $article->update(['is_published' => ! $article->is_published]);
        return response()->json([
            'success'      => true,
            'is_published' => $article->is_published,
        ]);
    }

    // ----------------------------------------
    // REORDER (AJAX — bulk)
    // ----------------------------------------
    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        foreach ($request->order as $position => $articleId) {
            Article::where('article_id', $articleId)
                ->update(['sort_order' => $position]);
        }
        return response()->json(['success' => true]);
    }
}