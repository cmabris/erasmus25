<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
            {{ __('Dashboard') }}
        </h1>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('common.admin.dashboard.welcome_message') }}
        </p>
    </div>

    {{-- Statistics Grid --}}
    <div class="mb-8 animate-fade-in" style="animation-delay: 0.1s;">
        <h2 id="statistics-heading" class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
            {{ __('common.admin.dashboard.statistics_title') }}
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" role="list" aria-labelledby="statistics-heading">
            {{-- Active Programs --}}
            <div class="animate-slide-up" style="animation-delay: 0.2s;" role="listitem">
                <x-ui.stat-card
                    :value="$activePrograms"
                    :label="__('common.admin.dashboard.stats.active_programs')"
                    icon="academic-cap"
                    color="primary"
                    variant="default"
                />
            </div>

            {{-- Open Calls --}}
            <div class="animate-slide-up" style="animation-delay: 0.3s;" role="listitem">
                <x-ui.stat-card
                    :value="$openCalls"
                    :label="__('common.admin.dashboard.stats.open_calls')"
                    icon="document-text"
                    color="success"
                    variant="default"
                />
            </div>

            {{-- Closed Calls --}}
            <div class="animate-slide-up" style="animation-delay: 0.4s;" role="listitem">
                <x-ui.stat-card
                    :value="$closedCalls"
                    :label="__('common.admin.dashboard.stats.closed_calls')"
                    icon="lock-closed"
                    color="neutral"
                    variant="default"
                />
            </div>

            {{-- News This Month --}}
            <div class="animate-slide-up" style="animation-delay: 0.5s;" role="listitem">
                <x-ui.stat-card
                    :value="$newsThisMonth"
                    :label="__('common.admin.dashboard.stats.news_this_month')"
                    icon="newspaper"
                    color="info"
                    variant="default"
                />
            </div>

            {{-- Available Documents --}}
            <div class="animate-slide-up" style="animation-delay: 0.6s;" role="listitem">
                <x-ui.stat-card
                    :value="$availableDocuments"
                    :label="__('common.admin.dashboard.stats.available_documents')"
                    icon="folder"
                    color="primary"
                    variant="default"
                />
            </div>

            {{-- Upcoming Events --}}
            <div class="animate-slide-up" style="animation-delay: 0.7s;" role="listitem">
                <x-ui.stat-card
                    :value="$upcomingEvents"
                    :label="__('common.admin.dashboard.stats.upcoming_events')"
                    icon="calendar"
                    color="warning"
                    variant="default"
                />
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mb-8 animate-fade-in" style="animation-delay: 0.2s;">
        <h2 id="quick-actions-heading" class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
            {{ __('common.admin.dashboard.quick_actions_title') }}
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" role="list" aria-labelledby="quick-actions-heading">
            {{-- Create Call --}}
            @if($this->canCreateCalls())
                @php
                    $callRoute = \Illuminate\Support\Facades\Route::has('admin.calls.create') ? route('admin.calls.create') : '#';
                @endphp
                <div class="animate-slide-up" style="animation-delay: 0.3s;" role="listitem">
                    <x-ui.card variant="elevated" hover class="cursor-pointer transition-all duration-200 hover:scale-105 focus-within:ring-2 focus-within:ring-erasmus-500 focus-within:ring-offset-2" wire:navigate href="{{ $callRoute }}" aria-label="{{ __('common.admin.dashboard.quick_actions.create_call') }}">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600 transition-colors dark:bg-green-900/30 dark:text-green-400" aria-hidden="true">
                                <flux:icon name="document-plus" class="[:where(&)]:size-6" variant="outline" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ __('common.admin.dashboard.quick_actions.create_call') }}
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('common.admin.dashboard.quick_actions.create_call_description') }}
                                </p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            @endif

            {{-- Create News --}}
            @if($this->canCreateNews())
                @php
                    $newsRoute = \Illuminate\Support\Facades\Route::has('admin.news.create') ? route('admin.news.create') : '#';
                @endphp
                <div class="animate-slide-up" style="animation-delay: 0.4s;" role="listitem">
                    <x-ui.card variant="elevated" hover class="cursor-pointer transition-all duration-200 hover:scale-105 focus-within:ring-2 focus-within:ring-erasmus-500 focus-within:ring-offset-2" wire:navigate href="{{ $newsRoute }}" aria-label="{{ __('common.admin.dashboard.quick_actions.create_news') }}">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 transition-colors dark:bg-blue-900/30 dark:text-blue-400" aria-hidden="true">
                                <flux:icon name="newspaper" class="[:where(&)]:size-6" variant="outline" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ __('common.admin.dashboard.quick_actions.create_news') }}
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('common.admin.dashboard.quick_actions.create_news_description') }}
                                </p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            @endif

            {{-- Create Document --}}
            @if($this->canCreateDocuments())
                @php
                    $documentRoute = \Illuminate\Support\Facades\Route::has('admin.documents.create') ? route('admin.documents.create') : '#';
                @endphp
                <div class="animate-slide-up" style="animation-delay: 0.5s;" role="listitem">
                    <x-ui.card variant="elevated" hover class="cursor-pointer transition-all duration-200 hover:scale-105 focus-within:ring-2 focus-within:ring-erasmus-500 focus-within:ring-offset-2" wire:navigate href="{{ $documentRoute }}" aria-label="{{ __('common.admin.dashboard.quick_actions.create_document') }}">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-purple-100 text-purple-600 transition-colors dark:bg-purple-900/30 dark:text-purple-400" aria-hidden="true">
                                <flux:icon name="document-arrow-up" class="[:where(&)]:size-6" variant="outline" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ __('common.admin.dashboard.quick_actions.create_document') }}
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('common.admin.dashboard.quick_actions.create_document_description') }}
                                </p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            @endif

            {{-- Create Event --}}
            @if($this->canCreateEvents())
                @php
                    $eventRoute = \Illuminate\Support\Facades\Route::has('admin.events.create') ? route('admin.events.create') : '#';
                @endphp
                <div class="animate-slide-up" style="animation-delay: 0.6s;" role="listitem">
                    <x-ui.card variant="elevated" hover class="cursor-pointer transition-all duration-200 hover:scale-105 focus-within:ring-2 focus-within:ring-erasmus-500 focus-within:ring-offset-2" wire:navigate href="{{ $eventRoute }}" aria-label="{{ __('common.admin.dashboard.quick_actions.create_event') }}">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 transition-colors dark:bg-amber-900/30 dark:text-amber-400" aria-hidden="true">
                                <flux:icon name="calendar" class="[:where(&)]:size-6" variant="outline" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ __('common.admin.dashboard.quick_actions.create_event') }}
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('common.admin.dashboard.quick_actions.create_event_description') }}
                                </p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            @endif

            {{-- Manage Programs --}}
            @if($this->canManagePrograms())
                @php
                    $programsRoute = \Illuminate\Support\Facades\Route::has('admin.programs.index') ? route('admin.programs.index') : '#';
                @endphp
                <div class="animate-slide-up" style="animation-delay: 0.7s;" role="listitem">
                    <x-ui.card variant="elevated" hover class="cursor-pointer transition-all duration-200 hover:scale-105 focus-within:ring-2 focus-within:ring-erasmus-500 focus-within:ring-offset-2" wire:navigate href="{{ $programsRoute }}" aria-label="{{ __('common.admin.dashboard.quick_actions.manage_programs') }}">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-erasmus-100 text-erasmus-600 transition-colors dark:bg-erasmus-900/30 dark:text-erasmus-400" aria-hidden="true">
                                <flux:icon name="academic-cap" class="[:where(&)]:size-6" variant="outline" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ __('common.admin.dashboard.quick_actions.manage_programs') }}
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('common.admin.dashboard.quick_actions.manage_programs_description') }}
                                </p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            @endif

            {{-- Manage Users (only for super-admin) --}}
            @if($this->canManageUsers())
                @php
                    $usersRoute = \Illuminate\Support\Facades\Route::has('admin.users.index') ? route('admin.users.index') : '#';
                @endphp
                <div class="animate-slide-up" style="animation-delay: 0.8s;" role="listitem">
                    <x-ui.card variant="elevated" hover class="cursor-pointer transition-all duration-200 hover:scale-105 focus-within:ring-2 focus-within:ring-erasmus-500 focus-within:ring-offset-2" wire:navigate href="{{ $usersRoute }}" aria-label="{{ __('common.admin.dashboard.quick_actions.manage_users') }}">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 transition-colors dark:bg-red-900/30 dark:text-red-400" aria-hidden="true">
                                <flux:icon name="users" class="[:where(&)]:size-6" variant="outline" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ __('common.admin.dashboard.quick_actions.manage_users') }}
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('common.admin.dashboard.quick_actions.manage_users_description') }}
                                </p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            @endif
        </div>
    </div>

    {{-- Alerts Section --}}
    @if($alerts->isNotEmpty())
        <div class="mb-8 animate-fade-in" style="animation-delay: 0.3s;" role="region" aria-labelledby="alerts-heading">
            <h2 id="alerts-heading" class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                {{ __('common.admin.dashboard.alerts_title') }}
            </h2>
            <div class="space-y-3" role="list" aria-label="{{ __('common.admin.dashboard.alerts_title') }}">
                @foreach($alerts as $index => $alert)
                    <div class="animate-slide-up" style="animation-delay: {{ 0.4 + ($index * 0.1) }}s;" role="listitem">
                        <flux:callout 
                            :variant="$alert['variant']" 
                            :icon="$alert['icon']"
                            :heading="$alert['title']"
                            role="alert"
                            aria-live="polite"
                        >
                            <flux:callout.text>
                                {{ $alert['description'] }}
                            </flux:callout.text>
                            @if($alert['url'])
                                <x-slot name="actions">
                                    <flux:button 
                                        :href="$alert['url']" 
                                        variant="ghost" 
                                        size="sm"
                                        wire:navigate
                                        aria-label="{{ __('common.actions.view') }}: {{ $alert['title'] }}"
                                    >
                                        {{ __('common.actions.view') }}
                                    </flux:button>
                                </x-slot>
                            @endif
                        </flux:callout>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recent Activity Section --}}
    <div class="mb-8 animate-fade-in" style="animation-delay: 0.4s;" role="region" aria-labelledby="activity-heading">
        <h2 id="activity-heading" class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
            {{ __('common.admin.dashboard.recent_activity_title') }}
        </h2>
        @if($recentActivities->isEmpty())
            <x-ui.empty-state 
                :title="__('common.admin.dashboard.activity.no_activity')"
                icon="clock"
                size="sm"
            />
        @else
            <x-ui.card>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700" role="list" aria-label="{{ __('common.admin.dashboard.recent_activity_title') }}">
                    @foreach($recentActivities as $index => $activity)
                        @php
                            $colorClasses = match($activity['color']) {
                                'success' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
                                'info' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                                'danger' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                'warning' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                                'primary' => 'bg-erasmus-100 text-erasmus-600 dark:bg-erasmus-900/30 dark:text-erasmus-400',
                                default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-900/30 dark:text-zinc-400',
                            };
                        @endphp
                        <div class="animate-slide-up flex items-start gap-4 p-4 transition-colors duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 focus-within:bg-zinc-50 dark:focus-within:bg-zinc-800/50" style="animation-delay: {{ 0.5 + ($index * 0.05) }}s;" role="listitem">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $colorClasses }}" aria-hidden="true">
                                <flux:icon :name="$activity['icon']" class="[:where(&)]:size-5" variant="outline" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                            @if($activity['url'])
                                                <a href="{{ $activity['url'] }}" class="hover:text-erasmus-600 dark:hover:text-erasmus-400 focus:outline-none focus:ring-2 focus:ring-erasmus-500 focus:ring-offset-2 rounded" wire:navigate aria-label="{{ __('common.actions.view') }}: {{ $activity['title'] }}">
                                                    {{ $activity['title'] }}
                                                </a>
                                            @else
                                                {{ $activity['title'] }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ ucfirst(__('common.admin.dashboard.activity.' . $activity['action'])) }} 
                                            {{ __('common.messages.by') }} 
                                            <span class="font-medium">{{ $activity['user'] }}</span>
                                        </p>
                                    </div>
                                    <time class="shrink-0 text-xs text-zinc-500 dark:text-zinc-400" datetime="{{ $activity['date']->toIso8601String() }}" aria-label="{{ format_datetime($activity['date']) }}">
                                        {{ $activity['date']->diffForHumans() }}
                                    </time>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Charts Section --}}
    <div class="mb-8 animate-fade-in" style="animation-delay: 0.5s;" role="region" aria-labelledby="charts-heading">
        <h2 id="charts-heading" class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
            {{ __('common.admin.dashboard.charts.monthly_activity_title') }}
        </h2>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2" role="list" aria-labelledby="charts-heading">
            {{-- Monthly Activity Chart --}}
            <div class="animate-slide-up" style="animation-delay: 0.6s;" role="listitem">
                <x-ui.card>
                    <div class="p-4">
                        <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ __('common.admin.dashboard.charts.monthly_activity_title') }}
                        </h3>
                        <div class="relative" role="img" aria-label="{{ __('common.admin.dashboard.charts.monthly_activity_title') }}">
                            <canvas id="monthlyActivityChart" class="max-h-64" aria-label="{{ __('common.admin.dashboard.charts.monthly_activity_title') }}"></canvas>
                            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-800/80 rounded-lg">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="animate-spin h-8 w-8 text-erasmus-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('common.messages.loading') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- Calls by Status Chart --}}
            <div class="animate-slide-up" style="animation-delay: 0.7s;" role="listitem">
                <x-ui.card>
                    <div class="p-4">
                        <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ __('common.admin.dashboard.charts.calls_by_status_title') }}
                        </h3>
                        <div class="relative" role="img" aria-label="{{ __('common.admin.dashboard.charts.calls_by_status_title') }}">
                            <canvas id="callsByStatusChart" class="max-h-64" aria-label="{{ __('common.admin.dashboard.charts.calls_by_status_title') }}"></canvas>
                            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-800/80 rounded-lg">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="animate-spin h-8 w-8 text-erasmus-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('common.messages.loading') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>

        {{-- Calls by Program Chart --}}
        @if(!empty($programData['data']) && count($programData['data']) > 0)
            <div class="mt-6 animate-slide-up" style="animation-delay: 0.8s;">
                <x-ui.card>
                    <div class="p-4">
                        <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ __('common.admin.dashboard.charts.calls_by_program_title') }}
                        </h3>
                        <div class="relative" role="img" aria-label="{{ __('common.admin.dashboard.charts.calls_by_program_title') }}">
                            <canvas id="callsByProgramChart" class="max-h-64" aria-label="{{ __('common.admin.dashboard.charts.calls_by_program_title') }}"></canvas>
                            <div wire:loading class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-800/80 rounded-lg">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="animate-spin h-8 w-8 text-erasmus-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('common.messages.loading') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        @endif
    </div>
</div>

@php
    $monthlyData = $this->getMonthlyActivityData();
    $statusData = $this->getCallsByStatusData();
    $programData = $this->getCallsByProgramData();
@endphp

@script
<script>
    // Store chart instances to prevent duplicates
    window.dashboardCharts = window.dashboardCharts || {};
    window.chartsInitializing = false;

    // Define function in global scope so it's available for event listeners
    window.initDashboardCharts = function() {
        // Prevent multiple simultaneous initializations
        if (window.chartsInitializing) {
            return;
        }

        // Check if Chart.js is available
        if (typeof window.Chart === 'undefined') {
            setTimeout(window.initDashboardCharts, 100); // Retry after 100ms
            return;
        }

        window.chartsInitializing = true;
        const Chart = window.Chart;
        const isDark = document.documentElement.classList.contains('dark') || 
                      window.matchMedia('(prefers-color-scheme: dark)').matches;

        // Monthly Activity Chart (Bar Chart)
        const monthlyCtx = document.getElementById('monthlyActivityChart');
        if (monthlyCtx) {
            // Destroy existing chart if it exists
            if (window.dashboardCharts.monthlyActivity) {
                window.dashboardCharts.monthlyActivity.destroy();
            }
            const monthlyData = @js($monthlyData);
            window.dashboardCharts.monthlyActivity = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: monthlyData.labels,
                    datasets: monthlyData.datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: isDark ? '#e4e4e7' : '#18181b',
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: isDark ? '#a1a1aa' : '#71717a',
                            },
                            grid: {
                                color: isDark ? '#3f3f46' : '#e4e4e7',
                            },
                        },
                        x: {
                            ticks: {
                                color: isDark ? '#a1a1aa' : '#71717a',
                            },
                            grid: {
                                color: isDark ? '#3f3f46' : '#e4e4e7',
                            },
                        },
                    },
                },
            });
        }

        // Calls by Status Chart (Doughnut Chart)
        const statusCtx = document.getElementById('callsByStatusChart');
        if (statusCtx) {
            // Destroy existing chart if it exists
            if (window.dashboardCharts.callsByStatus) {
                window.dashboardCharts.callsByStatus.destroy();
            }
            const statusData = @js($statusData);
            window.dashboardCharts.callsByStatus = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusData.labels,
                    datasets: [{
                        data: statusData.data,
                        backgroundColor: statusData.colors,
                        borderWidth: 2,
                        borderColor: isDark ? '#27272a' : '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: isDark ? '#e4e4e7' : '#18181b',
                                padding: 15,
                            },
                        },
                    },
                },
            });
        }

        // Calls by Program Chart (Bar Chart)
        const programCtx = document.getElementById('callsByProgramChart');
        if (programCtx) {
            // Destroy existing chart if it exists
            if (window.dashboardCharts.callsByProgram) {
                window.dashboardCharts.callsByProgram.destroy();
            }
            const programData = @js($programData);
            if (programData.data && programData.data.length > 0) {
                window.dashboardCharts.callsByProgram = new Chart(programCtx, {
                    type: 'bar',
                    data: {
                        labels: programData.labels,
                        datasets: [{
                            label: @js(__('common.admin.dashboard.charts.calls')),
                            data: programData.data,
                            backgroundColor: programData.colors,
                            borderColor: programData.colors.map(color => color.replace('0.8', '1')),
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    color: isDark ? '#a1a1aa' : '#71717a',
                                },
                                grid: {
                                    color: isDark ? '#3f3f46' : '#e4e4e7',
                                },
                            },
                            x: {
                                ticks: {
                                    color: isDark ? '#a1a1aa' : '#71717a',
                                },
                                grid: {
                                    color: isDark ? '#3f3f46' : '#e4e4e7',
                                },
                            },
                        },
                    },
                });
            }
        }

        window.chartsInitializing = false;
    };

    // Initialize charts when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(window.initDashboardCharts, 300);
        });
    } else {
        // DOM already loaded
        setTimeout(window.initDashboardCharts, 300);
    }

    // Also initialize when Livewire finishes updating (only once)
    let livewireInitialized = false;
    document.addEventListener('livewire:init', function() {
        if (!livewireInitialized) {
            livewireInitialized = true;
            setTimeout(window.initDashboardCharts, 300);
        }
    });

    // Also try after Livewire updates
    document.addEventListener('livewire:navigated', function() {
        setTimeout(window.initDashboardCharts, 500);
    });
</script>
@endscript
