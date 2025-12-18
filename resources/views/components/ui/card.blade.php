@props([
    'variant' => 'default', // default, elevated, bordered, flat
    'padding' => 'md', // none, sm, md, lg, xl
    'rounded' => 'lg', // none, sm, md, lg, xl, full
    'hover' => false, // Enable hover effect
    'clickable' => false, // Make entire card clickable
    'href' => null, // Link URL if clickable
])

@php
    $baseClasses = 'block w-full transition-all duration-200';
    
    $variantClasses = match($variant) {
        'elevated' => 'bg-white dark:bg-zinc-800 shadow-lg dark:shadow-zinc-900/50',
        'bordered' => 'bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700',
        'flat' => 'bg-zinc-50 dark:bg-zinc-800/50',
        default => 'bg-white dark:bg-zinc-800 shadow-sm dark:shadow-zinc-900/30 border border-zinc-100 dark:border-zinc-700/50',
    };
    
    $paddingClasses = match($padding) {
        'none' => '',
        'sm' => 'p-3 sm:p-4',
        'lg' => 'p-6 sm:p-8',
        'xl' => 'p-8 sm:p-10',
        default => 'p-4 sm:p-6', // md
    };
    
    $roundedClasses = match($rounded) {
        'none' => '',
        'sm' => 'rounded-sm',
        'md' => 'rounded-md',
        'xl' => 'rounded-xl',
        'full' => 'rounded-3xl',
        default => 'rounded-lg', // lg
    };
    
    $hoverClasses = $hover 
        ? 'hover:shadow-md dark:hover:shadow-zinc-900/60 hover:border-zinc-200 dark:hover:border-zinc-600' 
        : '';
    
    $clickableClasses = $clickable 
        ? 'cursor-pointer hover:scale-[1.01] active:scale-[0.99]' 
        : '';
    
    $classes = collect([
        $baseClasses,
        $variantClasses,
        $paddingClasses,
        $roundedClasses,
        $hoverClasses,
        $clickableClasses,
    ])->filter()->implode(' ');
    
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} 
    {{ $attributes->merge(['class' => $classes]) }}
    @if($href) href="{{ $href }}" wire:navigate @endif
>
    @if(isset($header))
        <div class="mb-4">
            {{ $header }}
        </div>
    @endif
    
    {{ $slot }}
    
    @if(isset($footer))
        <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-700">
            {{ $footer }}
        </div>
    @endif
</{{ $tag }}>
