@props([
    'call' => null,
    'title' => null,
    'slug' => null,
    'type' => null, // alumnado, personal
    'modality' => null, // corta, larga
    'status' => null, // borrador, abierta, cerrada, en_baremacion, resuelta, archivada
    'numberOfPlaces' => null,
    'destinations' => [],
    'estimatedStartDate' => null,
    'estimatedEndDate' => null,
    'program' => null,
    'academicYear' => null,
    'href' => null,
    'variant' => 'default', // default, compact, featured
    'showProgram' => true,
])

@php
    // Capture variant and unset to prevent propagation to child components
    $cardVariant = $variant;
    unset($variant);
    
    // Extract from model if provided
    $title = $call?->title ?? $title;
    $slug = $call?->slug ?? $slug;
    $type = $call?->type ?? $type;
    $modality = $call?->modality ?? $modality;
    $status = $call?->status ?? $status;
    $numberOfPlaces = $call?->number_of_places ?? $numberOfPlaces;
    $destinations = $call?->destinations ?? $destinations;
    $estimatedStartDate = $call?->estimated_start_date ?? $estimatedStartDate;
    $estimatedEndDate = $call?->estimated_end_date ?? $estimatedEndDate;
    $program = $call?->program ?? $program;
    $academicYear = $call?->academicYear ?? $academicYear;
    $href = $href ?? ($slug ? route('home') : null); // TODO: Change to calls.show
    
    // Status configuration
    $statusConfig = match($status) {
        'abierta' => ['color' => 'success', 'label' => __('Abierta'), 'icon' => 'check-circle'],
        'cerrada' => ['color' => 'danger', 'label' => __('Cerrada'), 'icon' => 'x-circle'],
        'en_baremacion' => ['color' => 'warning', 'label' => __('En baremación'), 'icon' => 'clock'],
        'resuelta' => ['color' => 'info', 'label' => __('Resuelta'), 'icon' => 'check-badge'],
        'archivada' => ['color' => 'neutral', 'label' => __('Archivada'), 'icon' => 'archive-box'],
        default => ['color' => 'neutral', 'label' => __('Borrador'), 'icon' => 'pencil-square'],
    };
    
    // Type labels
    $typeLabel = match($type) {
        'alumnado' => __('Alumnado'),
        'personal' => __('Personal'),
        default => null,
    };
    
    // Modality labels
    $modalityLabel = match($modality) {
        'corta' => __('Corta duración'),
        'larga' => __('Larga duración'),
        default => null,
    };
    
    // Format dates
    $startDateFormatted = $estimatedStartDate ? \Carbon\Carbon::parse($estimatedStartDate)->translatedFormat('M Y') : null;
    $endDateFormatted = $estimatedEndDate ? \Carbon\Carbon::parse($estimatedEndDate)->translatedFormat('M Y') : null;
    
    // Destinations count
    $destinationsArray = is_array($destinations) ? $destinations : [];
    $destinationsCount = count($destinationsArray);
@endphp

@if($cardVariant === 'featured')
    {{-- Featured variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        variant="bordered"
        {{ $attributes->except(['variant', 'call', 'showProgram'])->class(['relative overflow-hidden']) }}
    >
        {{-- Status indicator bar --}}
        <div @class([
            'absolute inset-x-0 top-0 h-1',
            'bg-green-500' => $status === 'abierta',
            'bg-red-500' => $status === 'cerrada',
            'bg-amber-500' => $status === 'en_baremacion',
            'bg-cyan-500' => $status === 'resuelta',
            'bg-zinc-400' => !in_array($status, ['abierta', 'cerrada', 'en_baremacion', 'resuelta']),
        ])></div>
        
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                {{-- Program & Year --}}
                @if($showProgram && ($program || $academicYear))
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        @if($program)
                            <x-ui.badge size="sm" color="primary">{{ $program->name ?? $program }}</x-ui.badge>
                        @endif
                        @if($academicYear)
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $academicYear->name ?? $academicYear }}
                            </span>
                        @endif
                    </div>
                @endif
                
                {{-- Title --}}
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white sm:text-xl">
                    {{ $title }}
                </h3>
                
                {{-- Meta info --}}
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($typeLabel)
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon name="user-group" class="[:where(&)]:size-4" variant="outline" />
                            {{ $typeLabel }}
                        </span>
                    @endif
                    @if($modalityLabel)
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon name="clock" class="[:where(&)]:size-4" variant="outline" />
                            {{ $modalityLabel }}
                        </span>
                    @endif
                    @if($numberOfPlaces)
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon name="users" class="[:where(&)]:size-4" variant="outline" />
                            {{ $numberOfPlaces }} {{ __('plazas') }}
                        </span>
                    @endif
                    @if($destinationsCount > 0)
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon name="map-pin" class="[:where(&)]:size-4" variant="outline" />
                            {{ $destinationsCount }} {{ trans_choice('destino|destinos', $destinationsCount) }}
                        </span>
                    @endif
                </div>
                
                {{-- Dates --}}
                @if($startDateFormatted || $endDateFormatted)
                    <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-zinc-100 px-3 py-1.5 text-sm dark:bg-zinc-800">
                        <flux:icon name="calendar" class="[:where(&)]:size-4 text-zinc-500" variant="outline" />
                        <span class="text-zinc-700 dark:text-zinc-300">
                            @if($startDateFormatted && $endDateFormatted)
                                {{ $startDateFormatted }} - {{ $endDateFormatted }}
                            @else
                                {{ $startDateFormatted ?? $endDateFormatted }}
                            @endif
                        </span>
                    </div>
                @endif
            </div>
            
            {{-- Status badge --}}
            <div class="shrink-0">
                <x-ui.badge 
                    :color="$statusConfig['color']" 
                    :icon="$statusConfig['icon']"
                    size="lg"
                >
                    {{ $statusConfig['label'] }}
                </x-ui.badge>
            </div>
        </div>
    </x-ui.card>

@elseif($cardVariant === 'compact')
    {{-- Compact variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="sm"
        {{ $attributes->except(['variant', 'call', 'showProgram']) }}
    >
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h3 class="truncate font-medium text-zinc-900 dark:text-white">{{ $title }}</h3>
                <div class="mt-1 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($typeLabel)
                        <span>{{ $typeLabel }}</span>
                    @endif
                    @if($numberOfPlaces)
                        <span>• {{ $numberOfPlaces }} {{ __('plazas') }}</span>
                    @endif
                </div>
            </div>
            <x-ui.badge 
                :color="$statusConfig['color']" 
                size="sm"
            >
                {{ $statusConfig['label'] }}
            </x-ui.badge>
        </div>
    </x-ui.card>

@else
    {{-- Default variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="true"
        {{ $attributes->except(['variant', 'call', 'showProgram']) }}
    >
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                @if($showProgram && $program)
                    <x-ui.badge size="sm" color="primary" class="mb-2">
                        {{ $program->name ?? $program }}
                    </x-ui.badge>
                @endif
                
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
                
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($typeLabel)
                        <span>{{ $typeLabel }}</span>
                    @endif
                    @if($numberOfPlaces)
                        <span>{{ $numberOfPlaces }} {{ __('plazas') }}</span>
                    @endif
                    @if($startDateFormatted)
                        <span>{{ $startDateFormatted }}</span>
                    @endif
                </div>
            </div>
            
            <x-ui.badge 
                :color="$statusConfig['color']" 
                :dot="true"
            >
                {{ $statusConfig['label'] }}
            </x-ui.badge>
        </div>
    </x-ui.card>
@endif
