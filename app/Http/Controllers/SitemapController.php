<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\Document;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Cache TTL for sitemap in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Generate the sitemap XML.
     */
    public function index(): Response
    {
        $content = Cache::remember('sitemap.xml', self::CACHE_TTL, function () {
            return view('sitemap.index', [
                'programs' => Program::where('is_active', true)
                    ->orderBy('order')
                    ->get(),
                'calls' => Call::whereIn('status', ['abierta', 'cerrada'])
                    ->whereNotNull('published_at')
                    ->orderBy('published_at', 'desc')
                    ->get(),
                'news' => NewsPost::where('status', 'publicado')
                    ->whereNotNull('published_at')
                    ->orderBy('published_at', 'desc')
                    ->get(),
                'documents' => Document::where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->get(),
                'events' => ErasmusEvent::where('is_public', true)
                    ->where('start_date', '>=', now()->subMonths(3))
                    ->orderBy('start_date', 'desc')
                    ->get(),
            ])->render();
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Clear the sitemap cache.
     * Call this when content is published/updated.
     */
    public static function clearCache(): void
    {
        Cache::forget('sitemap.xml');
    }
}
