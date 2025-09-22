<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    // Dashboard management page for super users
    public function index(): Response
    {
        return Inertia::render('ContentManagement', [
            'videos' => Video::orderBy('category')->orderBy('sort_order')->get(),
            'articles' => Article::orderBy('category')->orderBy('sort_order')->get(),
        ]);
    }

    // API endpoint to get content for dashboard
    public function getContent(): JsonResponse
    {
        $videos = [
            'manajemen' => Video::active()->byCategory('manajemen')->ordered()->get(['id', 'title', 'youtube_id']),
            'kesehatan' => Video::active()->byCategory('kesehatan')->ordered()->get(['id', 'title', 'youtube_id']),
            'budidaya' => Video::active()->byCategory('budidaya')->ordered()->get(['id', 'title', 'youtube_id']),
        ];

        $articles = [
            'manajemen' => Article::active()->published()->byCategory('manajemen')->ordered()->get(),
            'kesehatan' => Article::active()->published()->byCategory('kesehatan')->ordered()->get(),
            'budidaya' => Article::active()->published()->byCategory('budidaya')->ordered()->get(),
        ];

        return response()->json([
            'videos' => $videos,
            'articles' => $articles,
        ]);
    }

    // Video management
    public function storeVideo(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'youtube_id' => 'required|string|max:255',
            'category' => 'required|in:manajemen,kesehatan,budidaya',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $video = Video::create($request->all());

        return response()->json([
            'message' => 'Video berhasil ditambahkan',
            'video' => $video,
        ]);
    }

    public function updateVideo(Request $request, Video $video): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'youtube_id' => 'required|string|max:255',
            'category' => 'required|in:manajemen,kesehatan,budidaya',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $video->update($request->all());

        return response()->json([
            'message' => 'Video berhasil diperbarui',
            'video' => $video,
        ]);
    }

    public function destroyVideo(Video $video): JsonResponse
    {
        $video->delete();

        return response()->json([
            'message' => 'Video berhasil dihapus',
        ]);
    }

    // Videos listing page
    public function videosIndex(): Response
    {
        return Inertia::render('videos/Index');
    }

    // Articles listing page
    public function articlesIndex(): Response
    {
        return Inertia::render('articles/Index');
    }

    // Article show page
    public function showArticle(Article $article): Response
    {
        // Only show active and published articles
        if (!$article->is_active || !$article->published_at || $article->published_at > now()) {
            abort(404);
        }

        return Inertia::render('articles/Show', [
            'article' => $article,
        ]);
    }

    // Article management
    public function storeArticle(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'author' => 'nullable|string|max:255',
            'image_url' => 'nullable|url',
            'category' => 'required|in:manajemen,kesehatan,budidaya',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'published_at' => 'nullable|date',
        ]);

        $article = Article::create($request->all());

        return response()->json([
            'message' => 'Artikel berhasil ditambahkan',
            'article' => $article,
        ]);
    }

    public function updateArticle(Request $request, Article $article): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'author' => 'nullable|string|max:255',
            'image_url' => 'nullable|url',
            'category' => 'required|in:manajemen,kesehatan,budidaya',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'published_at' => 'nullable|date',
        ]);

        $article->update($request->all());

        return response()->json([
            'message' => 'Artikel berhasil diperbarui',
            'article' => $article,
        ]);
    }

    public function destroyArticle(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json([
            'message' => 'Artikel berhasil dihapus',
        ]);
    }
}
