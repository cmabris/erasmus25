@props([
    'news' => null,
    'title' => null,
    'slug' => null,
    'excerpt' => null,
    'country' => null,
    'city' => null,
    'publishedAt' => null,
    'program' => null,
    'author' => null,
    'imageUrl' => null,
    'href' => null,
    'variant' => 'default', // default, compact, featured, horizontal
    'showProgram' => true,
    'showAuthor' => false,
    'showDate' => true,
])

@php
    // Capture variant and unset to prevent propagation to child components
    $cardVariant = $variant;
    unset($variant);
    
    // Extract from model if provided
    $title = $news?->title ?? $title;
    $slug = $news?->slug ?? $slug;
    $excerpt = $news?->excerpt ?? $excerpt;
    $country = $news?->country ?? $country;
    $city = $news?->city ?? $city;
    $publishedAt = $news?->published_at ?? $publishedAt;
    $program = $news?->program ?? $program;
    $author = $news?->author ?? $author;
    
    // Generate href - use route if available, otherwise fallback
    if ($href) {
        // href already provided
    } elseif ($slug && $news) {
        // Try to use news.show route if available
        try {
            $href = route('noticias.show', $news);
        } catch (\Illuminate\Routing\Exceptions\RouteNotFoundException $e) {
            $href = null; // Route not yet defined
        }
    } else {
        $href = null;
    }
    
    // Format date
    $dateFormatted = $publishedAt ? \Carbon\Carbon::parse($publishedAt)->translatedFormat('d M Y') : null;
    $dateRelative = $publishedAt ? \Carbon\Carbon::parse($publishedAt)->diffForHumans() : null;
    
    // Location
    $location = collect([$city, $country])->filter()->implode(', ');
@endphp

@if($cardVariant === 'featured')
    {{-- Featured variant - large card with image --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="none"
        {{ $attributes->except(['variant', 'news', 'showProgram', 'showAuthor', 'showDate'])->class(['overflow-hidden']) }}
    >
        {{-- Image --}}
        @if($imageUrl)
            <div class="aspect-[16/9] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                <img 
                    src="{{ $imageUrl }}" 
                    alt="{{ $title }}" 
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    loading="lazy"
                >
            </div>
        @else
            <div class="flex aspect-[16/9] w-full items-center justify-center bg-gradient-to-br from-erasmus-100 to-erasmus-200 dark:from-erasmus-900/50 dark:to-erasmus-800/50">
                <flux:icon name="newspaper" class="[:where(&)]:size-16 text-erasmus-400 dark:text-erasmus-600" variant="outline" />
            </div>
        @endif
        
        <div class="p-6">
            {{-- Meta --}}
            <div class="mb-3 flex flex-wrap items-center gap-2">
                @if($showProgram && $program)
                    <x-ui.badge size="sm" color="primary">{{ $program->name ?? $program }}</x-ui.badge>
                @endif
                @if($location)
                    <span class="inline-flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400">
                        <flux:icon name="map-pin" class="[:where(&)]:size-3.5" variant="outline" />
                        {{ $location }}
                    </span>
                @endif
            </div>
            
            {{-- Title --}}
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
                {{ $title }}
            </h3>
            
            {{-- Excerpt --}}
            @if($excerpt)
                <p class="mt-3 line-clamp-3 text-zinc-600 dark:text-zinc-400">
                    {{ $excerpt }}
                </p>
            @endif
            
            {{-- Footer --}}
            <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-700">
                @if($showDate && $dateFormatted)
                    <time datetime="{{ $publishedAt }}" class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $dateFormatted }}
                    </time>
                @endif
                
                <span class="inline-flex items-center gap-1 text-sm font-medium text-erasmus-600 dark:text-erasmus-400">
                    {{ __('common.actions.read_more') }}
                    <flux:icon name="arrow-right" class="[:where(&)]:size-4" variant="outline" />
                </span>
            </div>
        </div>
    </x-ui.card>

@elseif($cardVariant === 'horizontal')
    {{-- Horizontal variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="none"
        {{ $attributes->except(['variant', 'news', 'showProgram', 'showAuthor', 'showDate'])->class(['overflow-hidden']) }}
    >
        <div class="flex flex-col sm:flex-row">
            {{-- Image --}}
            <div class="shrink-0 sm:w-48 md:w-56">
                @if($imageUrl)
                    <div class="aspect-[16/9] h-full w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 sm:aspect-auto">
                        <img 
                            src="{{ $imageUrl }}" 
                            alt="{{ $title }}" 
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </div>
                @else
                    <div class="flex aspect-[16/9] h-full w-full items-center justify-center bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700 sm:aspect-auto">
                        <flux:icon name="newspaper" class="[:where(&)]:size-10 text-zinc-400" variant="outline" />
                    </div>
                @endif
            </div>
            
            {{-- Content --}}
            <div class="flex flex-1 flex-col justify-between p-4 sm:p-5">
                <div>
                    @if($showProgram && $program)
                        <x-ui.badge size="sm" color="primary" class="mb-2">{{ $program->name ?? $program }}</x-ui.badge>
                    @endif
                    
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
                    
                    @if($excerpt)
                        <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $excerpt }}
                        </p>
                    @endif
                </div>
                
                <div class="mt-3 flex items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($showDate && $dateRelative)
                        <time datetime="{{ $publishedAt }}">{{ $dateRelative }}</time>
                    @endif
                    @if($location)
                        <span class="inline-flex items-center gap-1">
                            <flux:icon name="map-pin" class="[:where(&)]:size-3.5" variant="outline" />
                            {{ $location }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-ui.card>

@elseif($cardVariant === 'compact')
    {{-- Compact variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="sm"
        {{ $attributes->except(['variant', 'news', 'showProgram', 'showAuthor', 'showDate']) }}
    >
        <div class="flex items-start gap-3">
            @if($imageUrl)
                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <img src="{{ $imageUrl }}" alt="{{ $title }}" class="h-full w-full object-cover" loading="lazy">
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <h3 class="truncate font-medium text-zinc-900 dark:text-white">{{ $title }}</h3>
                @if($showDate && $dateRelative)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $dateRelative }}</p>
                @endif
            </div>
        </div>
    </x-ui.card>

@else
    {{-- Default variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="none"
        {{ $attributes->except(['variant', 'news', 'showProgram', 'showAuthor', 'showDate'])->class(['overflow-hidden']) }}
    >
        {{-- Image --}}
        @if($imageUrl)
            <div class="aspect-[16/10] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                <img 
                    src="{{ $imageUrl }}" 
                    alt="{{ $title }}" 
                    class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                    loading="lazy"
                >
            </div>
        @else
            <div class="flex aspect-[16/10] w-full items-center justify-center bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700">
                <flux:icon name="newspaper" class="[:where(&)]:size-12 text-zinc-400" variant="outline" />
            </div>
        @endif
        
        <div class="p-4 sm:p-5">
            @if($showProgram && $program)
                <x-ui.badge size="sm" color="primary" class="mb-2">{{ $program->name ?? $program }}</x-ui.badge>
            @endif
            
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
            
            @if($excerpt)
                <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ $excerpt }}
                </p>
            @endif
            
            @if($showDate && $dateRelative)
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $dateRelative }}
                </p>
            @endif
        </div>
    </x-ui.card>
@endif
