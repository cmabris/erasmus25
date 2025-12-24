@props([
    'resolution' => null,
    'variant' => 'default', // default, compact
])

@php
    $resolution = $resolution ?? null;
    $variant = $variant ?? 'default';
    
    if (!$resolution) {
        return;
    }
    
    $typeLabel = match($resolution->type ?? '') {
        'provisional' => __('common.resolution.provisional'),
        'definitiva' => __('common.resolution.definitive'),
        'rectificativa' => __('common.resolution.rectificative'),
        default => ucfirst($resolution->type ?? __('common.resolution.provisional')),
    };
    
    $hasFile = false; // TODO: Check if resolution has media file when Media Library is implemented
@endphp

@if($variant === 'compact')
    <x-ui.card variant="bordered" padding="sm" {{ $attributes }}>
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h4 class="font-semibold text-zinc-900 dark:text-white">
                    {{ $resolution->title }}
                </h4>
                @if($resolution->official_date)
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('common.resolution.official_date') }} {{ $resolution->official_date->translatedFormat('d M Y') }}
                    </p>
                @endif
            </div>
            <div class="flex shrink-0 items-center gap-2">
                @if($typeLabel)
                    <x-ui.badge size="sm" color="primary">
                        {{ $typeLabel }}
                    </x-ui.badge>
                @endif
                @if($hasFile)
                    <x-ui.button 
                        href="#" 
                        variant="ghost" 
                        size="sm"
                        icon="arrow-down-tray"
                    >
                        {{ __('common.resolution.download') }}
                    </x-ui.button>
                @endif
            </div>
        </div>
    </x-ui.card>
@else
    <x-ui.card variant="bordered" {{ $attributes }}>
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-erasmus-100 dark:bg-erasmus-900/30">
                        <flux:icon name="document-text" class="[:where(&)]:size-5 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-zinc-900 dark:text-white">
                                {{ $resolution->title }}
                            </h4>
                            @if($typeLabel)
                                <x-ui.badge size="sm" color="primary">
                                    {{ $typeLabel }}
                                </x-ui.badge>
                            @endif
                        </div>
                        @if($resolution->callPhase)
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('common.resolution.phase') }} {{ $resolution->callPhase->name }}
                            </p>
                        @endif
                    </div>
                </div>
                
                @if($resolution->description)
                    <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $resolution->description }}
                    </p>
                @endif
                
                @if($resolution->evaluation_procedure)
                    <div class="mt-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('common.resolution.evaluation_procedure') }}
                        </p>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $resolution->evaluation_procedure }}
                        </p>
                    </div>
                @endif
                
                <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                    @if($resolution->official_date)
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon name="calendar" class="[:where(&)]:size-3" variant="outline" />
                            <span class="font-medium">{{ __('common.resolution.official_date') }}</span>
                            {{ $resolution->official_date->translatedFormat('d M Y') }}
                        </span>
                    @endif
                    @if($resolution->published_at)
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon name="globe-alt" class="[:where(&)]:size-3" variant="outline" />
                            <span class="font-medium">{{ __('common.resolution.published') }}</span>
                            {{ $resolution->published_at->translatedFormat('d M Y') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        @if($hasFile)
            <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <x-ui.button 
                    href="#" 
                    variant="primary"
                    icon="arrow-down-tray"
                    size="sm"
                >
                    {{ __('common.resolution.download_pdf') }}
                </x-ui.button>
            </div>
        @endif
    </x-ui.card>
@endif
