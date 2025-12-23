<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="calendar-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#calendar-pattern)" width="100%" height="100%" />
            </svg>
        </div>
        
        {{-- Decorative elements --}}
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-gold-500/10 blur-3xl"></div>
        
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            {{-- Breadcrumbs --}}
            <div class="mb-8">
                <x-ui.breadcrumbs 
                    :items="[
                        ['label' => __('Calendario'), 'href' => route('calendario')],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <flux:icon name="calendar" class="[:where(&)]:size-5" variant="outline" />
                    {{ __('Calendario de Eventos') }}
                </div>
                
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    {{ __('Calendario Erasmus+') }}
                </h1>
                
                <p class="mt-4 text-lg leading-relaxed text-erasmus-100 sm:text-xl">
                    {{ __('Consulta todas las fechas importantes de los programas Erasmus+. Navega por meses, semanas o días para ver los eventos detallados.') }}
                </p>
            </div>
            
            {{-- Stats Row --}}
            <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6">
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['this_month'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('Este mes') }}</div>
                </div>
                <div class="col-span-2 rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:col-span-2 sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['upcoming'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('Próximos eventos') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Filters and Navigation --}}
    <section class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            {{-- Calendar Navigation --}}
            <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <button 
                        wire:click="previous"
                        type="button"
                        class="rounded-lg border border-zinc-300 bg-white p-2 text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                    >
                        <flux:icon name="chevron-left" class="[:where(&)]:size-5" variant="outline" />
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                            @if($viewMode === 'month')
                                {{ $this->currentDateCarbon->translatedFormat('F Y') }}
                            @elseif($viewMode === 'week')
                                {{ __('Semana del') }} {{ $this->currentDateCarbon->copy()->startOfWeek()->translatedFormat('d F Y') }}
                            @else
                                {{ $this->currentDateCarbon->translatedFormat('l, d F Y') }}
                            @endif
                        </h2>
                    </div>
                    
                    <button 
                        wire:click="next"
                        type="button"
                        class="rounded-lg border border-zinc-300 bg-white p-2 text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                    >
                        <flux:icon name="chevron-right" class="[:where(&)]:size-5" variant="outline" />
                    </button>
                    
                    <button 
                        wire:click="goToToday"
                        type="button"
                        class="rounded-lg border border-erasmus-300 bg-erasmus-50 px-4 py-2 text-sm font-medium text-erasmus-700 transition-colors hover:bg-erasmus-100 dark:border-erasmus-600 dark:bg-erasmus-900/30 dark:text-erasmus-300 dark:hover:bg-erasmus-900/50"
                    >
                        {{ __('Hoy') }}
                    </button>
                </div>
                
                {{-- View Mode Selector --}}
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Vista:') }}</label>
                    <div class="inline-flex rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-700">
                        <button
                            wire:click="changeView('month')"
                            type="button"
                            class="rounded px-3 py-1.5 text-sm font-medium transition-colors {{ $viewMode === 'month' 
                                ? 'bg-erasmus-600 text-white' 
                                : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600' 
                            }}"
                        >
                            {{ __('Mes') }}
                        </button>
                        <button
                            wire:click="changeView('week')"
                            type="button"
                            class="rounded px-3 py-1.5 text-sm font-medium transition-colors {{ $viewMode === 'week' 
                                ? 'bg-erasmus-600 text-white' 
                                : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600' 
                            }}"
                        >
                            {{ __('Semana') }}
                        </button>
                        <button
                            wire:click="changeView('day')"
                            type="button"
                            class="rounded px-3 py-1.5 text-sm font-medium transition-colors {{ $viewMode === 'day' 
                                ? 'bg-erasmus-600 text-white' 
                                : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600' 
                            }}"
                        >
                            {{ __('Día') }}
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Program Filter --}}
                @if($this->availablePrograms->isNotEmpty())
                    <div class="flex items-center gap-2">
                        <label for="program-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ __('Programa:') }}
                        </label>
                        <select 
                            id="program-filter"
                            wire:model.live="selectedProgram"
                            class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                        >
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->availablePrograms as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                
                {{-- Event Type Filter --}}
                <div class="flex items-center gap-2">
                    <label for="type-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ __('Tipo:') }}
                    </label>
                    <select 
                        id="type-filter"
                        wire:model.live="selectedEventType"
                        class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                    >
                        <option value="">{{ __('Todos') }}</option>
                        @foreach($this->eventTypes as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Reset Filters --}}
                @if($selectedProgram || $selectedEventType)
                    <button 
                        wire:click="resetFilters"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="x-mark" class="[:where(&)]:size-4" variant="outline" />
                        {{ __('Limpiar') }}
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- Calendar View --}}
    <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
        @if($viewMode === 'month')
            {{-- Month View --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                {{-- Weekday Headers --}}
                <div class="grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-700">
                    @foreach(['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $day)
                        <div class="border-r border-zinc-200 px-4 py-3 text-center text-sm font-semibold text-zinc-700 last:border-r-0 dark:border-zinc-700 dark:text-zinc-300">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>
                
                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7">
                    @foreach($this->calendarDays as $day)
                        <div @class([
                            'min-h-[120px] border-r border-b border-zinc-200 p-2 dark:border-zinc-700',
                            'bg-zinc-50 dark:bg-zinc-900/50' => !$day['isCurrentMonth'],
                            'bg-white dark:bg-zinc-800' => $day['isCurrentMonth'],
                        ])>
                            <div class="mb-2 flex items-center justify-between">
                                <span @class([
                                    'text-sm font-medium',
                                    'text-zinc-400 dark:text-zinc-600' => !$day['isCurrentMonth'],
                                    'text-zinc-900 dark:text-white' => $day['isCurrentMonth'] && !$day['isToday'],
                                    'rounded-full bg-erasmus-600 px-2 py-0.5 text-white' => $day['isToday'],
                                ])>
                                    {{ $day['date']->format('d') }}
                                </span>
                                @if($day['eventsCount'] > 0)
                                    <span class="rounded-full bg-erasmus-100 px-2 py-0.5 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                                        {{ $day['eventsCount'] }}
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Events List --}}
                            <div class="space-y-1">
                                @php
                                    // Get events for this day - handle both Collection and array
                                    $dayEvents = $day['events'];
                                    if (!($dayEvents instanceof \Illuminate\Support\Collection)) {
                                        $dayEvents = collect($dayEvents);
                                    }
                                    $dayEvents = $dayEvents->take(3);
                                @endphp
                                @foreach($dayEvents as $event)
                                    @php
                                        // Handle both object and array formats (Livewire serialization)
                                        $eventId = is_object($event) ? $event->id : ($event['id'] ?? null);
                                        $eventType = is_object($event) ? $event->event_type : ($event['event_type'] ?? 'otro');
                                        $eventTitle = is_object($event) ? $event->title : ($event['title'] ?? '');
                                        $eventStartDate = is_object($event) ? $event->start_date : ($event['start_date'] ?? null);
                                        $startTime = $eventStartDate ? (\Carbon\Carbon::parse($eventStartDate)->format('H:i')) : null;
                                    @endphp
                                    @if($eventId)
                                    <a 
                                        href="{{ route('eventos.show', $eventId) }}"
                                        class="block truncate rounded px-1.5 py-0.5 text-xs transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                        @class([
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $eventType === 'apertura' || $eventType === 'publicacion_definitivo',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $eventType === 'cierre',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $eventType === 'entrevista',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' => $eventType === 'publicacion_provisional',
                                            'bg-erasmus-100 text-erasmus-800 dark:bg-erasmus-900/30 dark:text-erasmus-300' => $eventType === 'reunion_informativa',
                                            'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' => $eventType === 'otro',
                                        ])
                                    >
                                        @if($startTime && $startTime !== '00:00')
                                            {{ $startTime }} 
                                        @endif
                                        {{ \Illuminate\Support\Str::limit($eventTitle, 20) }}
                                    </a>
                                    @endif
                                @endforeach
                                @if($day['eventsCount'] > 3)
                                    <a 
                                        href="{{ route('eventos.index', ['desde' => $day['date']->format('Y-m-d'), 'hasta' => $day['date']->format('Y-m-d')]) }}"
                                        class="block text-xs text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300"
                                    >
                                        +{{ $day['eventsCount'] - 3 }} {{ __('más') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
        @elseif($viewMode === 'week')
            {{-- Week View --}}
            <div class="space-y-6">
                @foreach($this->weekDays as $day)
                    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                        <div @class([
                            'border-b px-6 py-4',
                            'bg-erasmus-50 dark:bg-erasmus-900/20' => $day['isToday'],
                            'border-zinc-200 dark:border-zinc-700' => !$day['isToday'],
                        ])>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $day['date']->translatedFormat('l, d F Y') }}
                                @if($day['isToday'])
                                    <span class="ml-2 rounded-full bg-erasmus-600 px-2 py-0.5 text-xs font-medium text-white">
                                        {{ __('Hoy') }}
                                    </span>
                                @endif
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            @if($day['events']->isEmpty())
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('No hay eventos este día') }}
                                </p>
                            @else
                                <div class="space-y-3">
                                    @foreach($day['events'] as $event)
                                        <x-content.event-card 
                                            :event="$event"
                                            :href="route('eventos.show', $event->id)"
                                            variant="compact"
                                        />
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
        @else
            {{-- Day View --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="border-b border-zinc-200 bg-erasmus-50 px-6 py-4 dark:border-zinc-700 dark:bg-erasmus-900/20">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ $this->currentDateCarbon->translatedFormat('l, d F Y') }}
                        <span class="ml-2 rounded-full bg-erasmus-600 px-2 py-0.5 text-xs font-medium text-white">
                            {{ __('Hoy') }}
                        </span>
                    </h3>
                </div>
                
                <div class="p-6">
                    @if($this->calendarEvents->isEmpty())
                        <x-ui.empty-state 
                            :title="__('No hay eventos este día')"
                            :description="__('No hay eventos programados para esta fecha.')"
                            icon="calendar"
                        />
                    @else
                        <div class="space-y-4">
                            @foreach($this->calendarEvents as $event)
                                <x-content.event-card 
                                    :event="$event"
                                    :href="route('eventos.show', $event->id)"
                                    variant="default"
                                />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.section>

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r from-gold-500 to-gold-600">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 lg:flex-row">
                <div class="text-center lg:text-left">
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">
                        {{ __('¿Prefieres ver el listado?') }}
                    </h2>
                    <p class="mt-2 text-gold-100">
                        {{ __('Consulta todos los eventos en formato de listado con filtros avanzados.') }}
                    </p>
                </div>
                <div class="flex flex-shrink-0 gap-3">
                    <x-ui.button 
                        href="{{ route('eventos.index') }}" 
                        variant="secondary"
                        navigate
                    >
                        {{ __('Ver listado') }}
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('convocatorias.index') }}" 
                        variant="ghost"
                        class="text-white hover:bg-white/10"
                        navigate
                    >
                        {{ __('Ver convocatorias') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</div>

