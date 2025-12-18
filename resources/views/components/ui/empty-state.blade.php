@props([
    'title' => __('No hay datos'),
    'description' => null,
    'icon' => 'inbox', // Flux icon name
    'size' => 'md', // sm, md, lg
    'action' => null, // Action button text
    'actionHref' => null, // Action button link
    'actionIcon' => null, // Action button icon
])

@php
    $containerClasses = match($size) {
        'sm' => 'py-6',
        'lg' => 'py-16',
        default => 'py-12', // md
    };
    
    $iconSizeClasses = match($size) {
        'sm' => '[:where(&)]:size-8',
        'lg' => '[:where(&)]:size-16',
        default => '[:where(&)]:size-12', // md
    };
    
    $titleClasses = match($size) {
        'sm' => 'text-sm',
        'lg' => 'text-xl',
        default => 'text-base', // md
    };
    
    $descriptionClasses = match($size) {
        'sm' => 'text-xs',
        'lg' => 'text-base',
        default => 'text-sm', // md
    };
@endphp

<div {{ $attributes->merge(['class' => "text-center $containerClasses"]) }}>
    <div class="flex justify-center">
        <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-4">
            <flux:icon :name="$icon" :class="$iconSizeClasses . ' text-zinc-400 dark:text-zinc-500'" />
        </div>
    </div>
    
    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white {{ $titleClasses }}">
        {{ $title }}
    </h3>
    
    @if($description)
        <p class="mt-2 text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto {{ $descriptionClasses }}">
            {{ $description }}
        </p>
    @endif
    
    {{ $slot }}
    
    @if($action)
        <div class="mt-6">
            <x-ui.button 
                :href="$actionHref" 
                :icon="$actionIcon"
                variant="primary"
            >
                {{ $action }}
            </x-ui.button>
        </div>
    @endif
</div>
