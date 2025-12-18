@props([
    'label' => '',
    'value' => '',
    'icon' => null, // Flux icon name
    'trend' => null, // 'up', 'down', or null
    'trendValue' => null, // e.g., '+12%'
    'description' => null, // Additional context
    'color' => 'primary', // primary, success, warning, danger, info, neutral
    'variant' => 'default', // default, compact, large
])

@php
    // Capture variant from props and unset to prevent conflicts with parent scope
    $statVariant = $variant;
    unset($variant);
    
    $iconBgClasses = match($color) {
        'success' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
        'warning' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'danger' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
        'info' => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400',
        'neutral' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400',
        default => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400', // primary
    };
    
    $trendColorClasses = match($trend) {
        'up' => 'text-green-600 dark:text-green-400',
        'down' => 'text-red-600 dark:text-red-400',
        default => 'text-zinc-500 dark:text-zinc-400',
    };
    
    $trendIcon = match($trend) {
        'up' => 'arrow-trending-up',
        'down' => 'arrow-trending-down',
        default => null,
    };
    
    $valueSizeClasses = match($statVariant) {
        'compact' => 'text-xl sm:text-2xl',
        'large' => 'text-4xl sm:text-5xl',
        default => 'text-2xl sm:text-3xl',
    };
    
    $iconSizeClasses = match($statVariant) {
        'compact' => 'p-2',
        'large' => 'p-4',
        default => 'p-3',
    };
    
    $iconInnerSize = match($statVariant) {
        'compact' => '[:where(&)]:size-5',
        'large' => '[:where(&)]:size-8',
        default => '[:where(&)]:size-6',
    };
@endphp

<x-ui.card {{ $attributes }}>
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 truncate">
                {{ $label }}
            </p>
            
            <div class="mt-2 flex items-baseline gap-2 flex-wrap">
                <p class="font-bold text-zinc-900 dark:text-white {{ $valueSizeClasses }}">
                    {{ $value }}
                </p>
                
                @if($trendValue)
                    <span class="inline-flex items-center gap-1 text-sm font-medium {{ $trendColorClasses }}">
                        @if($trendIcon)
                            <flux:icon :name="$trendIcon" class="[:where(&)]:size-4" variant="outline" />
                        @endif
                        {{ $trendValue }}
                    </span>
                @endif
            </div>
            
            @if($description)
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $description }}
                </p>
            @endif
        </div>
        
        @if($icon)
            <div class="shrink-0 rounded-lg {{ $iconSizeClasses }} {{ $iconBgClasses }}">
                <flux:icon :name="$icon" :class="$iconInnerSize" variant="outline" />
            </div>
        @endif
    </div>
    
    @if(isset($footer))
        <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-700">
            {{ $footer }}
        </div>
    @endif
</x-ui.card>
