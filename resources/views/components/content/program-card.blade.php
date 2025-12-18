@props([
    'program' => null,
    'name' => null,
    'code' => null,
    'description' => null,
    'slug' => null,
    'isActive' => true,
    'href' => null,
    'variant' => 'default', // default, compact, featured
    'showBadge' => true,
])

@php
    // Capture variant and unset to prevent propagation to child components
    $cardVariant = $variant;
    unset($variant);
    
    // Extract from model if provided
    $name = $program?->name ?? $name;
    $code = $program?->code ?? $code;
    $description = $program?->description ?? $description;
    $slug = $program?->slug ?? $slug;
    $isActive = $program?->is_active ?? $isActive;
    $href = $href ?? ($slug ? route('home') : null); // TODO: Change to programas.show
    
    // Program type icons and colors based on code
    $programConfig = match(true) {
        str_contains($code ?? '', 'KA1') => [
            'icon' => 'academic-cap',
            'color' => 'blue',
            'gradient' => 'from-blue-500 to-blue-600',
            'bgLight' => 'bg-blue-50 dark:bg-blue-900/20',
            'textColor' => 'text-blue-600 dark:text-blue-400',
        ],
        str_contains($code ?? '', 'VET') => [
            'icon' => 'briefcase',
            'color' => 'emerald',
            'gradient' => 'from-emerald-500 to-emerald-600',
            'bgLight' => 'bg-emerald-50 dark:bg-emerald-900/20',
            'textColor' => 'text-emerald-600 dark:text-emerald-400',
        ],
        str_contains($code ?? '', 'HED') => [
            'icon' => 'building-library',
            'color' => 'violet',
            'gradient' => 'from-violet-500 to-violet-600',
            'bgLight' => 'bg-violet-50 dark:bg-violet-900/20',
            'textColor' => 'text-violet-600 dark:text-violet-400',
        ],
        default => [
            'icon' => 'globe-europe-africa',
            'color' => 'erasmus',
            'gradient' => 'from-erasmus-500 to-erasmus-600',
            'bgLight' => 'bg-erasmus-50 dark:bg-erasmus-900/20',
            'textColor' => 'text-erasmus-600 dark:text-erasmus-400',
        ],
    };
@endphp

@if($cardVariant === 'featured')
    {{-- Featured variant - large card with gradient background --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="none" 
        {{ $attributes->except(['variant', 'program', 'showBadge'])->class(['overflow-hidden']) }}
    >
        <div class="relative">
            {{-- Gradient header --}}
            <div class="bg-gradient-to-br {{ $programConfig['gradient'] }} px-6 py-8 text-white sm:px-8 sm:py-10">
                <div class="flex items-start justify-between">
                    <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                        <flux:icon :name="$programConfig['icon']" class="[:where(&)]:size-8" />
                    </div>
                    @if($showBadge && $code)
                        <span class="rounded-full bg-white/20 px-3 py-1 text-sm font-medium backdrop-blur-sm">
                            {{ $code }}
                        </span>
                    @endif
                </div>
                <h3 class="mt-6 text-xl font-bold sm:text-2xl">{{ $name }}</h3>
            </div>
            
            {{-- Content --}}
            <div class="p-6 sm:p-8">
                @if($description)
                    <p class="line-clamp-3 text-sm text-zinc-600 dark:text-zinc-400 sm:text-base">
                        {{ $description }}
                    </p>
                @endif
                
                <div class="mt-6 flex items-center justify-between">
                    @if(!$isActive)
                        <x-ui.badge color="neutral">{{ __('Inactivo') }}</x-ui.badge>
                    @else
                        <x-ui.badge color="success" :dot="true">{{ __('Activo') }}</x-ui.badge>
                    @endif
                    
                    <span class="inline-flex items-center gap-1 text-sm font-medium {{ $programConfig['textColor'] }}">
                        {{ __('Ver programa') }}
                        <flux:icon name="arrow-right" class="[:where(&)]:size-4" />
                    </span>
                </div>
            </div>
        </div>
    </x-ui.card>

@elseif($cardVariant === 'compact')
    {{-- Compact variant - horizontal card --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="sm"
        {{ $attributes->except(['variant', 'program', 'showBadge']) }}
    >
        <div class="flex items-center gap-4">
            <div class="shrink-0 rounded-lg p-2.5 {{ $programConfig['bgLight'] }}">
                <flux:icon :name="$programConfig['icon']" class="[:where(&)]:size-5 {{ $programConfig['textColor'] }}" />
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="truncate font-semibold text-zinc-900 dark:text-white">{{ $name }}</h3>
                @if($code)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $code }}</p>
                @endif
            </div>
            <flux:icon name="chevron-right" class="[:where(&)]:size-5 shrink-0 text-zinc-400" />
        </div>
    </x-ui.card>

@else
    {{-- Default variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="true"
        {{ $attributes->except(['variant', 'program', 'showBadge']) }}
    >
        <div class="flex items-start gap-4">
            <div class="shrink-0 rounded-xl p-3 {{ $programConfig['bgLight'] }}">
                <flux:icon :name="$programConfig['icon']" class="[:where(&)]:size-6 {{ $programConfig['textColor'] }}" />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $name }}</h3>
                    @if($showBadge && $code)
                        <x-ui.badge size="sm" color="neutral">{{ $code }}</x-ui.badge>
                    @endif
                </div>
                @if($description)
                    <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>
        
        @if($href)
            <div class="mt-4 flex items-center justify-end">
                <span class="inline-flex items-center gap-1 text-sm font-medium {{ $programConfig['textColor'] }}">
                    {{ __('Más información') }}
                    <flux:icon name="arrow-right" class="[:where(&)]:size-4" />
                </span>
            </div>
        @endif
    </x-ui.card>
@endif
