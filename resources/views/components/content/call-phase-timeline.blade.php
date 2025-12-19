@props([
    'phases' => [],
    'variant' => 'default', // default, compact
])

@php
    $phases = is_array($phases) ? collect($phases) : $phases;
    $variant = $variant ?? 'default';
@endphp

@if($phases->isEmpty())
    <x-ui.empty-state 
        :title="__('No hay fases disponibles')"
        :description="__('Esta convocatoria no tiene fases definidas.')"
        icon="calendar"
        size="sm"
    />
@else
    <div class="space-y-4">
        @foreach($phases as $index => $phase)
            @php
                $isCurrent = $phase->is_current ?? false;
                $isPast = $phase->end_date && $phase->end_date->isPast();
                $isUpcoming = $phase->start_date && $phase->start_date->isFuture();
                
                $phaseStatus = match(true) {
                    $isCurrent => 'current',
                    $isPast => 'past',
                    $isUpcoming => 'upcoming',
                    default => 'default',
                };
                
                $statusConfig = match($phaseStatus) {
                    'current' => [
                        'border' => 'border-l-4 border-emerald-500',
                        'bg' => 'bg-emerald-50 dark:bg-emerald-900/10',
                        'badge' => 'bg-emerald-500 text-white',
                        'icon' => 'check-circle',
                    ],
                    'past' => [
                        'border' => 'border-l-4 border-zinc-300 dark:border-zinc-600',
                        'bg' => 'bg-zinc-50 dark:bg-zinc-800/50',
                        'badge' => 'bg-zinc-500 text-white',
                        'icon' => 'check',
                    ],
                    'upcoming' => [
                        'border' => 'border-l-4 border-amber-300 dark:border-amber-600',
                        'bg' => 'bg-amber-50 dark:bg-amber-900/10',
                        'badge' => 'bg-amber-500 text-white',
                        'icon' => 'clock',
                    ],
                    default => [
                        'border' => 'border-l-4 border-zinc-300 dark:border-zinc-600',
                        'bg' => 'bg-white dark:bg-zinc-800',
                        'badge' => 'bg-zinc-400 text-white',
                        'icon' => 'calendar',
                    ],
                };
            @endphp
            
            <div class="rounded-lg border {{ $statusConfig['border'] }} {{ $statusConfig['bg'] }} p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-3">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $statusConfig['badge'] }}">
                                <flux:icon :name="$statusConfig['icon']" class="[:where(&)]:size-4" variant="outline" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $phase->name }}
                                </h4>
                                @if($phase->phase_type)
                                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ ucfirst(str_replace('_', ' ', $phase->phase_type)) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        @if($phase->description)
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $phase->description }}
                            </p>
                        @endif
                        
                        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                            @if($phase->start_date)
                                <span class="inline-flex items-center gap-1.5">
                                    <flux:icon name="calendar" class="[:where(&)]:size-3" variant="outline" />
                                    <span class="font-medium">{{ __('Inicio:') }}</span>
                                    {{ $phase->start_date->translatedFormat('d M Y') }}
                                </span>
                            @endif
                            @if($phase->end_date)
                                <span class="inline-flex items-center gap-1.5">
                                    <flux:icon name="calendar" class="[:where(&)]:size-3" variant="outline" />
                                    <span class="font-medium">{{ __('Fin:') }}</span>
                                    {{ $phase->end_date->translatedFormat('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    @if($isCurrent)
                        <x-ui.badge color="success" size="sm">
                            {{ __('Fase actual') }}
                        </x-ui.badge>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
