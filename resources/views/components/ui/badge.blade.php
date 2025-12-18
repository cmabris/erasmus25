@props([
    'color' => 'neutral', // primary, success, warning, danger, info, neutral
    'size' => 'md', // xs, sm, md, lg
    'rounded' => 'full', // none, sm, md, lg, full
    'dot' => false, // Show colored dot before text
    'icon' => null, // Flux icon name
    'iconTrailing' => null, // Flux icon name for trailing icon
    'outline' => false, // Outline variant
])

@php
    $baseClasses = 'inline-flex items-center font-medium whitespace-nowrap';
    
    // Solid variants
    $solidColorClasses = match($color) {
        'primary' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'info' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
        default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300', // neutral
    };
    
    // Outline variants
    $outlineColorClasses = match($color) {
        'primary' => 'border border-blue-300 text-blue-700 dark:border-blue-700 dark:text-blue-400',
        'success' => 'border border-green-300 text-green-700 dark:border-green-700 dark:text-green-400',
        'warning' => 'border border-amber-300 text-amber-700 dark:border-amber-700 dark:text-amber-400',
        'danger' => 'border border-red-300 text-red-700 dark:border-red-700 dark:text-red-400',
        'info' => 'border border-cyan-300 text-cyan-700 dark:border-cyan-700 dark:text-cyan-400',
        default => 'border border-zinc-300 text-zinc-600 dark:border-zinc-600 dark:text-zinc-400', // neutral
    };
    
    $colorClasses = $outline ? $outlineColorClasses : $solidColorClasses;
    
    $sizeClasses = match($size) {
        'xs' => 'text-[10px] px-1.5 py-0.5 gap-1',
        'sm' => 'text-xs px-2 py-0.5 gap-1',
        'lg' => 'text-sm px-3 py-1.5 gap-2',
        default => 'text-xs px-2.5 py-1 gap-1.5', // md
    };
    
    $roundedClasses = match($rounded) {
        'none' => '',
        'sm' => 'rounded-sm',
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
        default => 'rounded-full', // full
    };
    
    $dotColorClasses = match($color) {
        'primary' => 'bg-blue-500',
        'success' => 'bg-green-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-cyan-500',
        default => 'bg-zinc-500', // neutral
    };
    
    $iconSizeClasses = match($size) {
        'xs' => '[:where(&)]:size-2.5',
        'sm' => '[:where(&)]:size-3',
        'lg' => '[:where(&)]:size-4',
        default => '[:where(&)]:size-3.5', // md
    };
    
    $classes = collect([
        $baseClasses,
        $colorClasses,
        $sizeClasses,
        $roundedClasses,
    ])->filter()->implode(' ');
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="shrink-0 size-1.5 rounded-full {{ $dotColorClasses }}"></span>
    @endif
    
    @if($icon)
        <flux:icon :name="$icon" :class="$iconSizeClasses" variant="mini" />
    @endif
    
    {{ $slot }}
    
    @if($iconTrailing)
        <flux:icon :name="$iconTrailing" :class="$iconSizeClasses" variant="mini" />
    @endif
</span>
