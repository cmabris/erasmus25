@props([
    'placeholder' => null,
    'size' => 'md', // sm, md, lg
    'icon' => 'magnifying-glass',
    'clearable' => true, // Show clear button when has value
    'loading' => false, // Show loading spinner
])

@php
    $sizeClasses = match($size) {
        'sm' => 'h-9 text-sm pl-9 pr-9',
        'lg' => 'h-12 text-base pl-12 pr-12',
        default => 'h-10 text-sm pl-10 pr-10', // md
    };
    
    $iconSizeClasses = match($size) {
        'sm' => '[:where(&)]:size-4 left-2.5',
        'lg' => '[:where(&)]:size-5 left-4',
        default => '[:where(&)]:size-4 left-3', // md
    };
    
    $clearButtonClasses = match($size) {
        'sm' => 'right-2',
        'lg' => 'right-3.5',
        default => 'right-2.5', // md
    };
    
    $placeholder = $placeholder ?? __('Buscar...');
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'relative']) }} x-data="{ value: @entangle($attributes->wire('model')) }">
    {{-- Search Icon --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center {{ $clearButtonClasses }}">
        @if($loading)
            <flux:icon name="arrow-path" class="{{ $iconSizeClasses }} animate-spin text-zinc-400" variant="outline" />
        @else
            <flux:icon :name="$icon" class="{{ $iconSizeClasses }} text-zinc-400 dark:text-zinc-500" variant="outline" />
        @endif
    </div>
    
    {{-- Input --}}
    <input 
        type="search"
        {{ $attributes->except('class')->merge([
            'class' => "w-full rounded-lg border border-zinc-300 bg-white placeholder-zinc-400 shadow-sm transition-colors focus:border-erasmus-500 focus:ring-erasmus-500 focus:ring-1 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-erasmus-500 $sizeClasses",
            'placeholder' => $placeholder,
        ]) }}
    />
    
    {{-- Clear button --}}
    @if($clearable)
        <button 
            type="button"
            x-show="value && value.length > 0"
            x-cloak
            @click="value = ''; $wire.set('{{ $attributes->wire('model')->value() }}', '')"
            class="absolute inset-y-0 flex items-center text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 {{ $clearButtonClasses }}"
        >
            <flux:icon name="x-circle" class="{{ $iconSizeClasses }}" variant="solid" />
            <span class="sr-only">{{ __('Limpiar búsqueda') }}</span>
        </button>
    @endif
</div>
