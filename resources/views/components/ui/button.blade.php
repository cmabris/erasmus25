@props([
    'variant' => 'primary', // primary, secondary, outline, ghost, danger, success
    'size' => 'md', // xs, sm, md, lg, xl
    'rounded' => 'lg', // none, sm, md, lg, full
    'icon' => null, // Flux icon name (leading)
    'iconTrailing' => null, // Flux icon name (trailing)
    'iconOnly' => false, // Button with only icon
    'loading' => false, // Show loading state
    'disabled' => false,
    'href' => null, // Make it a link
    'type' => 'button', // button, submit, reset
    'fullWidth' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = match($variant) {
        'secondary' => 'bg-zinc-100 text-zinc-900 hover:bg-zinc-200 focus:ring-zinc-500 dark:bg-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-600',
        'outline' => 'border border-zinc-300 text-zinc-700 hover:bg-zinc-50 focus:ring-zinc-500 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800',
        'ghost' => 'text-zinc-700 hover:bg-zinc-100 focus:ring-zinc-500 dark:text-zinc-300 dark:hover:bg-zinc-800',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-600',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-600',
        default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 dark:bg-blue-600 dark:hover:bg-blue-500', // primary
    };
    
    $sizeClasses = match($size) {
        'xs' => $iconOnly ? 'p-1' : 'text-xs px-2 py-1 gap-1',
        'sm' => $iconOnly ? 'p-1.5' : 'text-sm px-3 py-1.5 gap-1.5',
        'lg' => $iconOnly ? 'p-3' : 'text-base px-5 py-2.5 gap-2',
        'xl' => $iconOnly ? 'p-4' : 'text-lg px-6 py-3 gap-2.5',
        default => $iconOnly ? 'p-2' : 'text-sm px-4 py-2 gap-2', // md
    };
    
    $roundedClasses = match($rounded) {
        'none' => '',
        'sm' => 'rounded-sm',
        'md' => 'rounded-md',
        'full' => 'rounded-full',
        default => 'rounded-lg', // lg
    };
    
    $iconSizeClasses = match($size) {
        'xs' => '[:where(&)]:size-3',
        'sm' => '[:where(&)]:size-4',
        'lg' => '[:where(&)]:size-5',
        'xl' => '[:where(&)]:size-6',
        default => '[:where(&)]:size-4', // md
    };
    
    $widthClasses = $fullWidth ? 'w-full' : '';
    
    $classes = collect([
        $baseClasses,
        $variantClasses,
        $sizeClasses,
        $roundedClasses,
        $widthClasses,
    ])->filter()->implode(' ');
    
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{ $attributes->merge([
        'class' => $classes,
        'type' => $href ? null : $type,
        'href' => $href,
        'disabled' => $disabled || $loading,
    ])->except($href ? ['type'] : ['href']) }}
    @if($href) wire:navigate @endif
>
    @if($loading)
        <svg class="animate-spin {{ $iconSizeClasses }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon)
        <flux:icon :name="$icon" :class="$iconSizeClasses" />
    @endif
    
    @if(!$iconOnly)
        {{ $slot }}
    @endif
    
    @if($iconTrailing && !$loading)
        <flux:icon :name="$iconTrailing" :class="$iconSizeClasses" />
    @endif
</{{ $tag }}>
