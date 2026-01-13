<?php

namespace App\Livewire\Admin;

use App\Models\Call;
use App\Models\Document;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use App\Support\Permissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class Dashboard extends Component
{
    /**
     * Total active programs.
     */
    public int $activePrograms = 0;

    /**
     * Total open calls.
     */
    public int $openCalls = 0;

    /**
     * Total closed calls.
     */
    public int $closedCalls = 0;

    /**
     * News published this month.
     */
    public int $newsThisMonth = 0;

    /**
     * Total available documents.
     */
    public int $availableDocuments = 0;

    /**
     * Upcoming events count.
     */
    public int $upcomingEvents = 0;

    /**
     * Recent activities.
     *
     * @var Collection<int, array<string, mixed>>
     */
    public Collection $recentActivities;

    /**
     * Alerts requiring attention.
     *
     * @var Collection<int, array<string, mixed>>
     */
    public Collection $alerts;

    /**
     * Initialize collections.
     */
    public function boot(): void
    {
        $this->recentActivities = collect();
        $this->alerts = collect();
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadStatistics();
        $this->loadRecentActivities();
        $this->loadAlerts();
    }

    /**
     * Cache TTL in seconds for dashboard statistics.
     */
    protected const CACHE_TTL_STATISTICS = 300; // 5 minutes

    /**
     * Cache TTL in seconds for chart data.
     */
    protected const CACHE_TTL_CHARTS = 900; // 15 minutes

    /**
     * Load all statistics with caching.
     */
    protected function loadStatistics(): void
    {
        $cacheKey = 'dashboard.statistics';

        $statistics = Cache::remember($cacheKey, self::CACHE_TTL_STATISTICS, function () {
            return [
                'activePrograms' => $this->getActiveProgramsCount(),
                'openCalls' => $this->getOpenCallsCount(),
                'closedCalls' => $this->getClosedCallsCount(),
                'newsThisMonth' => $this->getNewsThisMonthCount(),
                'availableDocuments' => $this->getAvailableDocumentsCount(),
                'upcomingEvents' => $this->getUpcomingEventsCount(),
            ];
        });

        $this->activePrograms = $statistics['activePrograms'];
        $this->openCalls = $statistics['openCalls'];
        $this->closedCalls = $statistics['closedCalls'];
        $this->newsThisMonth = $statistics['newsThisMonth'];
        $this->availableDocuments = $statistics['availableDocuments'];
        $this->upcomingEvents = $statistics['upcomingEvents'];
    }

    /**
     * Get count of active programs.
     */
    protected function getActiveProgramsCount(): int
    {
        return Program::query()
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get count of open calls.
     */
    protected function getOpenCallsCount(): int
    {
        return Call::query()
            ->where('status', 'abierta')
            ->whereNotNull('published_at')
            ->count();
    }

    /**
     * Get count of closed calls.
     */
    protected function getClosedCallsCount(): int
    {
        return Call::query()
            ->where('status', 'cerrada')
            ->count();
    }

    /**
     * Get count of news published this month.
     */
    protected function getNewsThisMonthCount(): int
    {
        return NewsPost::query()
            ->where('status', 'publicado')
            ->whereNotNull('published_at')
            ->whereMonth('published_at', now()->month)
            ->whereYear('published_at', now()->year)
            ->count();
    }

    /**
     * Get count of available documents.
     */
    protected function getAvailableDocumentsCount(): int
    {
        return Document::query()
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get count of upcoming events.
     */
    protected function getUpcomingEventsCount(): int
    {
        return ErasmusEvent::query()
            ->where('is_public', true)
            ->where('start_date', '>=', now()->startOfDay())
            ->count();
    }

    /**
     * Check if user can access users management.
     */
    public function canManageUsers(): bool
    {
        return auth()->user()?->can(Permissions::USERS_VIEW) ?? false;
    }

    /**
     * Check if user can create calls.
     */
    public function canCreateCalls(): bool
    {
        return auth()->user()?->can(Permissions::CALLS_CREATE) ?? false;
    }

    /**
     * Check if user can create news.
     */
    public function canCreateNews(): bool
    {
        return auth()->user()?->can(Permissions::NEWS_CREATE) ?? false;
    }

    /**
     * Check if user can create documents.
     */
    public function canCreateDocuments(): bool
    {
        return auth()->user()?->can(Permissions::DOCUMENTS_CREATE) ?? false;
    }

    /**
     * Check if user can create events.
     */
    public function canCreateEvents(): bool
    {
        return auth()->user()?->can(Permissions::EVENTS_CREATE) ?? false;
    }

    /**
     * Check if user can manage programs.
     */
    public function canManagePrograms(): bool
    {
        return auth()->user()?->can(Permissions::PROGRAMS_VIEW) ?? false;
    }

    /**
     * Load recent activities from the system.
     */
    protected function loadRecentActivities(): void
    {
        $activities = collect();

        // Get activities from Spatie Activitylog
        $activityLogs = Activity::query()
            ->with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($activityLogs as $activity) {
            if ($activity->subject) {
                $activities->push([
                    'type' => $this->getActivityType($activity->subject_type),
                    'action' => $activity->description,
                    'title' => $this->getModelTitle($activity->subject),
                    'user' => $activity->causer?->name ?? __('common.messages.system'),
                    'date' => $activity->created_at,
                    'url' => $this->getModelUrl($activity->subject_type, $activity->subject_id),
                    'icon' => $this->getActivityIcon($activity->subject_type),
                    'color' => $this->getActivityColor($activity->description),
                ]);
            }
        }

        // If we don't have enough activities from Activity, supplement with direct queries
        if ($activities->count() < 8) {
            $activities = $this->supplementActivities($activities);
        }

        $this->recentActivities = $activities;
    }

    /**
     * Supplement activities with direct model queries.
     */
    protected function supplementActivities(Collection $activities): Collection
    {
        // Recent calls
        $recentCalls = Call::query()
            ->with(['program', 'academicYear'])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentCalls as $call) {
            $activities->push([
                'type' => 'call',
                'action' => $call->published_at ? 'published' : 'updated',
                'title' => $call->title,
                'user' => __('common.messages.system'),
                'date' => $call->updated_at,
                'url' => \Illuminate\Support\Facades\Route::has('admin.calls.show') ? route('admin.calls.show', $call) : null,
                'icon' => 'document-text',
                'color' => $call->status === 'abierta' ? 'success' : 'neutral',
            ]);
        }

        // Recent news
        $recentNews = NewsPost::query()
            ->with(['program', 'author'])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentNews as $news) {
            $activities->push([
                'type' => 'news',
                'action' => $news->published_at ? 'published' : 'updated',
                'title' => $news->title,
                'user' => $news->author?->name ?? __('common.messages.system'),
                'date' => $news->updated_at,
                'url' => \Illuminate\Support\Facades\Route::has('admin.news.show') ? route('admin.news.show', $news) : null,
                'icon' => 'newspaper',
                'color' => $news->status === 'publicado' ? 'success' : 'neutral',
            ]);
        }

        // Recent documents
        $recentDocuments = Document::query()
            ->with(['category', 'program'])
            ->orderBy('updated_at', 'desc')
            ->limit(2)
            ->get();

        foreach ($recentDocuments as $document) {
            $activities->push([
                'type' => 'document',
                'action' => 'created',
                'title' => $document->title,
                'user' => __('common.messages.system'),
                'date' => $document->updated_at,
                'url' => \Illuminate\Support\Facades\Route::has('admin.documents.show') ? route('admin.documents.show', $document) : null,
                'icon' => 'folder',
                'color' => 'primary',
            ]);
        }

        // Sort by date descending and return
        return $activities->sortByDesc('date')->take(8);
    }

    /**
     * Get activity type from model class name.
     */
    protected function getActivityType(string $modelType): string
    {
        return match ($modelType) {
            Call::class => 'call',
            NewsPost::class => 'news',
            Document::class => 'document',
            ErasmusEvent::class => 'event',
            Program::class => 'program',
            default => 'other',
        };
    }

    /**
     * Get model title for display.
     */
    protected function getModelTitle(mixed $model): string
    {
        return match (true) {
            $model instanceof Call => $model->title,
            $model instanceof NewsPost => $model->title,
            $model instanceof Document => $model->title,
            $model instanceof ErasmusEvent => $model->title,
            $model instanceof Program => $model->name,
            default => __('common.messages.no_data'),
        };
    }

    /**
     * Get model URL if route exists.
     */
    protected function getModelUrl(string $modelType, int $modelId): ?string
    {
        $routeName = match ($modelType) {
            Call::class => 'admin.calls.show',
            NewsPost::class => 'admin.news.show',
            Document::class => 'admin.documents.show',
            ErasmusEvent::class => 'admin.events.show',
            Program::class => 'admin.programs.show',
            default => null,
        };

        if ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
            return route($routeName, $modelId);
        }

        return null;
    }

    /**
     * Get icon for activity type.
     */
    protected function getActivityIcon(string $modelType): string
    {
        return match ($modelType) {
            Call::class => 'document-text',
            NewsPost::class => 'newspaper',
            Document::class => 'folder',
            ErasmusEvent::class => 'calendar',
            Program::class => 'academic-cap',
            default => 'circle',
        };
    }

    /**
     * Get color for activity action.
     */
    protected function getActivityColor(string $action): string
    {
        $actionLower = strtolower($action);

        return match ($actionLower) {
            'created', 'publish', 'published', 'restore', 'restored' => 'success',
            'updated' => 'info',
            'deleted', 'archive', 'archived' => 'danger',
            default => 'neutral',
        };
    }

    /**
     * Load alerts requiring attention.
     */
    protected function loadAlerts(): void
    {
        $alerts = collect();

        // Calls closing soon (within 7 days)
        $callsClosingSoon = Call::query()
            ->where('status', 'abierta')
            ->whereNotNull('published_at')
            ->whereNotNull('closed_at')
            ->where('closed_at', '<=', now()->addDays(7))
            ->where('closed_at', '>', now())
            ->orderBy('closed_at')
            ->limit(5)
            ->get();

        foreach ($callsClosingSoon as $call) {
            $daysLeft = max(0, now()->diffInDays($call->closed_at, false));
            $alerts->push([
                'type' => 'call_closing_soon',
                'priority' => $daysLeft <= 3 ? 'high' : 'medium',
                'title' => __('common.admin.dashboard.alerts.call_closing_soon', ['title' => $call->title, 'days' => format_number($daysLeft)]),
                'description' => __('common.admin.dashboard.alerts.call_closing_soon_description', ['date' => format_date($call->closed_at)]),
                'url' => \Illuminate\Support\Facades\Route::has('admin.calls.show') ? route('admin.calls.show', $call) : null,
                'icon' => 'clock',
                'variant' => $daysLeft <= 3 ? 'danger' : 'warning',
            ]);
        }

        // Unpublished drafts older than 7 days
        $oldDrafts = Call::query()
            ->where('status', 'borrador')
            ->whereNull('published_at')
            ->where('created_at', '<', now()->subDays(7))
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        foreach ($oldDrafts as $call) {
            $daysOld = max(0, now()->diffInDays($call->created_at));
            $alerts->push([
                'type' => 'unpublished_draft',
                'priority' => 'medium',
                'title' => __('common.admin.dashboard.alerts.unpublished_draft', ['title' => $call->title, 'days' => format_number($daysOld)]),
                'description' => __('common.admin.dashboard.alerts.unpublished_draft_description'),
                'url' => \Illuminate\Support\Facades\Route::has('admin.calls.edit') ? route('admin.calls.edit', $call) : null,
                'icon' => 'document-text',
                'variant' => 'warning',
            ]);
        }

        // Upcoming events without location
        $eventsWithoutLocation = ErasmusEvent::query()
            ->where('is_public', true)
            ->where('start_date', '>=', now()->startOfDay())
            ->where('start_date', '<=', now()->addDays(7))
            ->whereNull('location')
            ->orderBy('start_date')
            ->limit(3)
            ->get();

        foreach ($eventsWithoutLocation as $event) {
            $alerts->push([
                'type' => 'event_missing_location',
                'priority' => 'low',
                'title' => __('common.admin.dashboard.alerts.event_missing_location', ['title' => $event->title]),
                'description' => __('common.admin.dashboard.alerts.event_missing_location_description', ['date' => format_date($event->start_date)]),
                'url' => \Illuminate\Support\Facades\Route::has('admin.events.edit') ? route('admin.events.edit', $event) : null,
                'icon' => 'map-pin',
                'variant' => 'info',
            ]);
        }

        $this->alerts = $alerts->sortBy(fn ($alert) => match ($alert['priority']) {
            'high' => 1,
            'medium' => 2,
            'low' => 3,
            default => 4,
        })->take(5);
    }

    /**
     * Get monthly activity data for charts with caching.
     *
     * @return array<string, mixed>
     */
    public function getMonthlyActivityData(): array
    {
        $cacheKey = 'dashboard.charts.monthly_activity';

        return Cache::remember($cacheKey, self::CACHE_TTL_CHARTS, function () {
            $months = collect();
            $now = now();

            // Generate last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $months->push($now->copy()->subMonths($i)->startOfMonth());
            }

            $labels = $months->map(fn ($date) => $date->translatedFormat('M Y'))->toArray();

            // Calls created per month
            $callsData = $months->map(function ($monthStart) {
                return Call::query()
                    ->whereBetween('created_at', [
                        $monthStart->copy()->startOfMonth(),
                        $monthStart->copy()->endOfMonth(),
                    ])
                    ->count();
            })->toArray();

            // News published per month
            $newsData = $months->map(function ($monthStart) {
                return NewsPost::query()
                    ->where('status', 'publicado')
                    ->whereNotNull('published_at')
                    ->whereBetween('published_at', [
                        $monthStart->copy()->startOfMonth(),
                        $monthStart->copy()->endOfMonth(),
                    ])
                    ->count();
            })->toArray();

            // Documents created per month
            $documentsData = $months->map(function ($monthStart) {
                return Document::query()
                    ->whereBetween('created_at', [
                        $monthStart->copy()->startOfMonth(),
                        $monthStart->copy()->endOfMonth(),
                    ])
                    ->count();
            })->toArray();

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => __('common.admin.dashboard.charts.calls'),
                        'data' => $callsData,
                        'backgroundColor' => 'rgba(0, 51, 153, 0.8)', // Erasmus blue
                        'borderColor' => 'rgba(0, 51, 153, 1)',
                    ],
                    [
                        'label' => __('common.admin.dashboard.charts.news'),
                        'data' => $newsData,
                        'backgroundColor' => 'rgba(34, 197, 94, 0.8)', // Green
                        'borderColor' => 'rgba(34, 197, 94, 1)',
                    ],
                    [
                        'label' => __('common.admin.dashboard.charts.documents'),
                        'data' => $documentsData,
                        'backgroundColor' => 'rgba(168, 85, 247, 0.8)', // Purple
                        'borderColor' => 'rgba(168, 85, 247, 1)',
                    ],
                ],
            ];
        });
    }

    /**
     * Get calls distribution by program with caching.
     *
     * @return array<string, mixed>
     */
    public function getCallsByProgramData(): array
    {
        $cacheKey = 'dashboard.charts.calls_by_program';

        return Cache::remember($cacheKey, self::CACHE_TTL_CHARTS, function () {
            $programs = Program::query()
                ->where('is_active', true)
                ->withCount(['calls' => function ($query) {
                    $query->whereNotNull('published_at');
                }])
                ->get()
                ->filter(fn ($program) => $program->calls_count > 0)
                ->sortByDesc('calls_count')
                ->take(5);

            return [
                'labels' => $programs->pluck('name')->toArray(),
                'data' => $programs->pluck('calls_count')->toArray(),
                'colors' => [
                    'rgba(0, 51, 153, 0.8)',   // Erasmus blue
                    'rgba(34, 197, 94, 0.8)',   // Green
                    'rgba(168, 85, 247, 0.8)',  // Purple
                    'rgba(251, 191, 36, 0.8)',  // Amber
                    'rgba(239, 68, 68, 0.8)',   // Red
                ],
            ];
        });
    }

    /**
     * Get calls distribution by status with caching.
     *
     * @return array<string, mixed>
     */
    public function getCallsByStatusData(): array
    {
        $cacheKey = 'dashboard.charts.calls_by_status';

        return Cache::remember($cacheKey, self::CACHE_TTL_CHARTS, function () {
            $statuses = [
                'abierta' => Call::query()
                    ->where('status', 'abierta')
                    ->whereNotNull('published_at')
                    ->count(),
                'cerrada' => Call::query()
                    ->where('status', 'cerrada')
                    ->count(),
                'borrador' => Call::query()
                    ->where('status', 'borrador')
                    ->whereNull('published_at')
                    ->count(),
            ];

            return [
                'labels' => [
                    __('common.admin.dashboard.charts.status_open'),
                    __('common.admin.dashboard.charts.status_closed'),
                    __('common.admin.dashboard.charts.status_draft'),
                ],
                'data' => array_values($statuses),
                'colors' => [
                    'rgba(34, 197, 94, 0.8)',   // Green for open
                    'rgba(107, 114, 128, 0.8)',  // Gray for closed
                    'rgba(251, 191, 36, 0.8)',  // Amber for draft
                ],
            ];
        });
    }

    /**
     * Clear dashboard cache.
     * This method can be called when data changes to invalidate the cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('dashboard.statistics');
        Cache::forget('dashboard.charts.monthly_activity');
        Cache::forget('dashboard.charts.calls_by_program');
        Cache::forget('dashboard.charts.calls_by_status');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.dashboard')
            ->layout('components.layouts.app', [
                'title' => __('Dashboard'),
            ]);
    }
}
