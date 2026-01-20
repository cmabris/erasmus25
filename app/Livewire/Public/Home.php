<?php

namespace App\Livewire\Public;

use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

class Home extends Component
{
    /**
     * Cache keys for home page data.
     */
    public const CACHE_KEY_CALLS = 'home.open_calls';

    public const CACHE_KEY_NEWS = 'home.recent_news';

    public const CACHE_KEY_EVENTS = 'home.upcoming_events';

    /**
     * Cache TTL in seconds (15 minutes for dynamic content).
     */
    public const CACHE_TTL = 900;

    /**
     * Active programs to display.
     *
     * @var Collection<int, Program>
     */
    public Collection $programs;

    /**
     * Open calls to display.
     *
     * @var Collection<int, Call>
     */
    public Collection $calls;

    /**
     * Recent news posts to display.
     *
     * @var Collection<int, NewsPost>
     */
    public Collection $news;

    /**
     * Upcoming events to display.
     *
     * @var Collection<int, ErasmusEvent>
     */
    public Collection $events;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadPrograms();
        $this->loadCalls();
        $this->loadNews();
        $this->loadEvents();
    }

    /**
     * Load active programs (using cached data).
     */
    protected function loadPrograms(): void
    {
        // Use cached programs and take only the first 6
        $this->programs = Program::getCachedActive()->take(6);
    }

    /**
     * Load open calls (cached for 15 minutes).
     */
    protected function loadCalls(): void
    {
        $this->calls = Cache::remember(self::CACHE_KEY_CALLS, self::CACHE_TTL, function () {
            return Call::query()
                ->with(['program', 'academicYear'])
                ->where('status', 'abierta')
                ->whereNotNull('published_at')
                ->orderBy('published_at', 'desc')
                ->limit(4)
                ->get();
        });
    }

    /**
     * Load recent news posts (cached for 15 minutes).
     */
    protected function loadNews(): void
    {
        $this->news = Cache::remember(self::CACHE_KEY_NEWS, self::CACHE_TTL, function () {
            return NewsPost::query()
                ->with(['program', 'author'])
                ->where('status', 'publicado')
                ->whereNotNull('published_at')
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get();
        });
    }

    /**
     * Load upcoming events (cached for 15 minutes).
     */
    protected function loadEvents(): void
    {
        $this->events = Cache::remember(self::CACHE_KEY_EVENTS, self::CACHE_TTL, function () {
            return ErasmusEvent::query()
                ->with(['program', 'call'])
                ->where('is_public', true)
                ->where('start_date', '>=', now()->startOfDay())
                ->orderBy('start_date')
                ->limit(5)
                ->get();
        });
    }

    /**
     * Clear home page cache.
     * Call this when calls, news, or events are published/updated.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_CALLS);
        Cache::forget(self::CACHE_KEY_NEWS);
        Cache::forget(self::CACHE_KEY_EVENTS);
    }

    /**
     * Get JSON-LD structured data for the organization.
     *
     * @return array<string, mixed>
     */
    protected function getOrganizationJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            'name' => config('app.name'),
            'url' => route('home'),
            'logo' => config('app.url').'/images/logo.png',
            'description' => __('Portal de gestión de movilidades Erasmus+ para alumnado y personal docente.'),
            'sameAs' => [
                // Add social media URLs here when available
            ],
        ];
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.home')
            ->layout('components.layouts.public', [
                'title' => __('Erasmus+ - Movilidad Internacional'),
                'description' => __('Portal de gestión de movilidades Erasmus+ para alumnado y personal docente. Descubre convocatorias, programas y oportunidades de movilidad internacional.'),
                'jsonLd' => $this->getOrganizationJsonLd(),
            ]);
    }
}
