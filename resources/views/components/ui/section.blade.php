@props([
    'title' => null,
    'description' => null,
    'padding' => 'lg', // none, sm, md, lg, xl
    'divided' => false, // Add top border
    'centered' => false, // Center title and description
])

@php
    $paddingClasses = match($padding) {
        'none' => '',
        'sm' => 'py-6 sm:py-8',
        'md' => 'py-8 sm:py-12',
        'xl' => 'py-16 sm:py-24',
        default => 'py-12 sm:py-16', // lg
    };
    
    $dividedClasses = $divided 
        ? 'border-t border-zinc-200 dark:border-zinc-700' 
        : '';
    
    $alignClasses = $centered ? 'text-center' : '';
@endphp

<section {{ $attributes->merge(['class' => "$paddingClasses $dividedClasses"]) }}>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if($title || $description || isset($actions))
            <div class="mb-8 sm:mb-12 {{ $alignClasses }} {{ !$centered ? 'sm:flex sm:items-end sm:justify-between' : '' }}">
                <div class="{{ !$centered ? 'sm:max-w-xl' : 'max-w-2xl mx-auto' }}">
                    @if($title)
                        <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-3xl">
                            {{ $title }}
                        </h2>
                    @endif
                    
                    @if($description)
                        <p class="mt-2 text-base text-zinc-600 dark:text-zinc-400 sm:text-lg">
                            {{ $description }}
                        </p>
                    @endif
                </div>
                
                @if(isset($actions))
                    <div class="{{ $centered ? 'mt-6 flex justify-center gap-4' : 'mt-4 sm:mt-0 flex gap-4' }}">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        @endif
        
        {{ $slot }}
    </div>
</section>
