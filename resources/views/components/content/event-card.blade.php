@props([
    'event' => null,
    'title' => null,
    'description' => null,
    'eventType' => null, // apertura, cierre, entrevista, publicacion_provisional, publicacion_definitivo, reunion_informativa, otro
    'startDate' => null,
    'endDate' => null,
    'location' => null,
    'isPublic' => true,
    'program' => null,
    'call' => null,
    'href' => null,
    'variant' => 'default', // default, compact, timeline, calendar
    'showCall' => true,
])

@php
    // Capture variant and unset to prevent propagation to child components
    $cardVariant = $variant;
    unset($variant);
    
    // Extract from model if provided
    $title = $event?->title ?? $title;
    $description = $event?->description ?? $description;
    $eventType = $event?->event_type ?? $eventType;
    $startDate = $event?->start_date ?? $startDate;
    $endDate = $event?->end_date ?? $endDate;
    $location = $event?->location ?? $location;
    $isPublic = $event?->is_public ?? $isPublic;
    $program = $event?->program ?? $program;
    $call = $event?->call ?? $call;
    $href = $href ?? ($event ? route('eventos.show', $event->id) : null);
    
    // Event type configuration
    $eventTypeConfig = match($eventType) {
        'apertura' => ['icon' => 'play-circle', 'color' => 'success', 'label' => __('common.events.opening')],
        'cierre' => ['icon' => 'stop-circle', 'color' => 'danger', 'label' => __('common.events.closing')],
        'entrevista' => ['icon' => 'chat-bubble-left-right', 'color' => 'info', 'label' => __('common.events.interview')],
        'publicacion_provisional' => ['icon' => 'document-text', 'color' => 'warning', 'label' => __('common.events.provisional_list')],
        'publicacion_definitivo' => ['icon' => 'document-check', 'color' => 'success', 'label' => __('common.events.definitive_list')],
        'reunion_informativa' => ['icon' => 'user-group', 'color' => 'primary', 'label' => __('common.events.info_meeting')],
        default => ['icon' => 'calendar', 'color' => 'neutral', 'label' => __('common.events.event')],
    };
    
    // Parse dates
    $startDateTime = $startDate ? \Carbon\Carbon::parse($startDate) : null;
    $endDateTime = $endDate ? \Carbon\Carbon::parse($endDate) : null;
    
    // Format dates
    $dayNumber = $startDateTime?->format('d');
    $monthShort = $startDateTime?->translatedFormat('M');
    $fullDate = $startDateTime?->translatedFormat('l, d F Y');
    $timeStart = $startDateTime?->format('H:i');
    $timeEnd = $endDateTime?->format('H:i');
    
    // Check if upcoming
    $isUpcoming = $startDateTime?->isFuture();
    $isToday = $startDateTime?->isToday();
    $isPast = $startDateTime?->isPast() && !$isToday;
@endphp

@if($cardVariant === 'calendar')
    {{-- Calendar variant - shows as calendar date card --}}
    <x-ui.card 
        :href="$href" 
        :hover="(bool)$href" 
        padding="none"
        {{ $attributes->except(['variant', 'event', 'showCall'])->class(['overflow-hidden text-center']) }}
    >
        {{-- Date header --}}
        <div @class([
            'px-4 py-3',
            'bg-erasmus-600 text-white' => $isUpcoming || $isToday,
            'bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' => $isPast,
        ])>
            <p class="text-sm font-medium uppercase">{{ $monthShort }}</p>
            <p class="text-3xl font-bold">{{ $dayNumber }}</p>
        </div>
        
        {{-- Content --}}
        <div class="p-4">
            <x-ui.badge size="sm" :color="$eventTypeConfig['color']" class="mb-2">
                {{ $eventTypeConfig['label'] }}
            </x-ui.badge>
            
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
            
            @if($timeStart)
                <p class="mt-2 inline-flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400">
                    <flux:icon name="clock" class="[:where(&)]:size-4" variant="outline" />
                    {{ $timeStart }}@if($timeEnd) - {{ $timeEnd }}@endif
                </p>
            @endif
        </div>
    </x-ui.card>

@elseif($cardVariant === 'timeline')
    {{-- Timeline variant --}}
    <div {{ $attributes->except(['variant', 'event', 'showCall'])->class(['relative flex gap-4 pb-8 last:pb-0']) }}>
        {{-- Timeline line --}}
        <div class="absolute bottom-0 left-5 top-10 w-px bg-zinc-200 dark:bg-zinc-700 last:hidden"></div>
        
        {{-- Date bubble --}}
        <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $eventTypeConfig['color'] === 'success' ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : ($eventTypeConfig['color'] === 'danger' ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : ($eventTypeConfig['color'] === 'warning' ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' : ($eventTypeConfig['color'] === 'info' ? 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400' : 'bg-erasmus-100 text-erasmus-600 dark:bg-erasmus-900/30 dark:text-erasmus-400'))) }}">
            <flux:icon :name="$eventTypeConfig['icon']" class="[:where(&)]:size-5" variant="outline" />
        </div>
        
        {{-- Content --}}
        <div class="flex-1 pt-1">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ $fullDate }}
                        @if($timeStart)
                            <span class="text-zinc-400 dark:text-zinc-500">• {{ $timeStart }}</span>
                        @endif
                    </p>
                    <h3 class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
                </div>
                @if($isToday)
                    <x-ui.badge color="success" size="sm">{{ __('common.events.today') }}</x-ui.badge>
                @endif
            </div>
            
            @if($description)
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $description }}</p>
            @endif
            
            @if($location)
                <p class="mt-2 inline-flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                    <flux:icon name="map-pin" class="[:where(&)]:size-4" variant="outline" />
                    {{ $location }}
                </p>
            @endif
            
            @if($showCall && $call)
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('common.events.call_label') }} {{ $call->title ?? $call }}
                </p>
            @endif
        </div>
    </div>

@elseif($cardVariant === 'compact')
    {{-- Compact variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="(bool)$href" 
        padding="sm"
        {{ $attributes->except(['variant', 'event', 'showCall']) }}
    >
        <div class="flex items-center gap-3">
            {{-- Date --}}
            <div @class([
                'flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg text-center',
                'bg-erasmus-100 text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300' => $isUpcoming || $isToday,
                'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' => $isPast,
            ])>
                <span class="text-lg font-bold leading-none">{{ $dayNumber }}</span>
                <span class="text-xs uppercase">{{ $monthShort }}</span>
            </div>
            
            {{-- Content --}}
            <div class="min-w-0 flex-1">
                <h3 class="truncate font-medium text-zinc-900 dark:text-white">{{ $title }}</h3>
                <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($timeStart)
                        <span>{{ $timeStart }}</span>
                    @endif
                    @if($location)
                        <span class="truncate">• {{ $location }}</span>
                    @endif
                </div>
            </div>
            
            {{-- Type indicator --}}
            <div class="shrink-0">
                <x-ui.badge size="sm" :color="$eventTypeConfig['color']" :icon="$eventTypeConfig['icon']" />
            </div>
        </div>
    </x-ui.card>

@else
    {{-- Default variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="(bool)$href"
        {{ $attributes->except(['variant', 'event', 'showCall']) }}
    >
        <div class="flex gap-4">
            {{-- Date block --}}
            <div @class([
                'flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl text-center',
                'bg-erasmus-100 text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300' => $isUpcoming || $isToday,
                'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' => $isPast,
            ])>
                <span class="text-2xl font-bold leading-none">{{ $dayNumber }}</span>
                <span class="text-sm uppercase">{{ $monthShort }}</span>
            </div>
            
            {{-- Content --}}
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <x-ui.badge size="sm" :color="$eventTypeConfig['color']" :icon="$eventTypeConfig['icon']">
                        {{ $eventTypeConfig['label'] }}
                    </x-ui.badge>
                    @if($isToday)
                        <x-ui.badge color="success" size="sm" :dot="true">{{ __('common.events.today') }}</x-ui.badge>
                    @endif
                </div>
                
                <h3 class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
                
                @if($description)
                    <p class="mt-1 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $description }}
                    </p>
                @endif
                
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($timeStart)
                        <span class="inline-flex items-center gap-1">
                            <flux:icon name="clock" class="[:where(&)]:size-4" variant="outline" />
                            {{ $timeStart }}@if($timeEnd) - {{ $timeEnd }}@endif
                        </span>
                    @endif
                    @if($location)
                        <span class="inline-flex items-center gap-1">
                            <flux:icon name="map-pin" class="[:where(&)]:size-4" variant="outline" />
                            {{ $location }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-ui.card>
@endif
