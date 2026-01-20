<div>
    {{-- Hero Section with Featured Image or Gradient --}}
    @if($this->featuredImage)
        <section class="relative h-[60vh] min-h-[400px] overflow-hidden">
            {{-- Featured Image (hero conversion - 1920x1080 WebP) --}}
            <img 
                src="{{ $this->featuredImage }}" 
                alt="{{ $newsPost->title }}"
                class="h-full w-full object-cover"
                loading="eager"
                decoding="async"
                fetchpriority="high"
            />
            
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-erasmus-900/90 via-erasmus-800/70 to-transparent"></div>
            
            {{-- Content --}}
            <div class="absolute inset-0 flex flex-col justify-end">
                <div class="mx-auto w-full max-w-7xl px-4 pb-16 sm:px-6 sm:pb-20 lg:px-8 lg:pb-24">
                    {{-- Breadcrumbs --}}
                    <div class="mb-8">
                        <x-ui.breadcrumbs 
                            :items="[
                                ['label' => __('common.nav.news'), 'href' => route('noticias.index')],
                                ['label' => $newsPost->title],
                            ]" 
                            class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                        />
                    </div>
                    
                    <div class="max-w-3xl">
                        {{-- Badges --}}
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            @if($newsPost->program)
                                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                                    <flux:icon name="academic-cap" class="[:where(&)]:size-5" variant="outline" />
                                    {{ $newsPost->program->name }}
                                </div>
                            @endif
                            @if($newsPost->academicYear)
                                <span class="rounded-full bg-white/20 px-3 py-1.5 text-sm font-semibold text-white backdrop-blur-sm">
                                    {{ $newsPost->academicYear->year }}
                                </span>
                            @endif
                            @if($newsPost->tags->isNotEmpty())
                                @foreach($newsPost->tags->take(3) as $tag)
                                    <span class="rounded-full bg-white/20 px-3 py-1.5 text-sm font-medium text-white backdrop-blur-sm">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                        
                        <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                            {{ $newsPost->title }}
                        </h1>
                        
                        {{-- Meta Information --}}
                        <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-white/80">
                            @if($newsPost->published_at)
                                <time datetime="{{ $newsPost->published_at->toIso8601String() }}" class="inline-flex items-center gap-2">
                                    <flux:icon name="calendar" class="[:where(&)]:size-4" variant="outline" />
                                    {{ $newsPost->published_at->translatedFormat('d F Y') }}
                                </time>
                            @endif
                            @if($newsPost->author)
                                <span class="inline-flex items-center gap-2">
                                    <flux:icon name="user" class="[:where(&)]:size-4" variant="outline" />
                                    {{ $newsPost->author->name }}
                                </span>
                            @endif
                            @if($newsPost->city || $newsPost->country)
                                <span class="inline-flex items-center gap-2">
                                    <flux:icon name="map-pin" class="[:where(&)]:size-4" variant="outline" />
                                    {{ collect([$newsPost->city, $newsPost->country])->filter()->implode(', ') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
        {{-- Hero Section with Gradient (no image) --}}
        <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
            {{-- Background pattern --}}
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="news-detail-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1" fill="currentColor" />
                        </pattern>
                    </defs>
                    <rect fill="url(#news-detail-pattern)" width="100%" height="100%" />
                </svg>
            </div>
            
            {{-- Decorative elements --}}
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            
            <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
                {{-- Breadcrumbs --}}
                <div class="mb-8">
                    <x-ui.breadcrumbs 
                        :items="[
                            ['label' => __('Noticias'), 'href' => route('noticias.index')],
                            ['label' => $newsPost->title],
                        ]" 
                        class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                    />
                </div>
                
                <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        {{-- Badges --}}
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            @if($newsPost->program)
                                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                                    <flux:icon name="academic-cap" class="[:where(&)]:size-5" variant="outline" />
                                    {{ $newsPost->program->name }}
                                </div>
                            @endif
                            @if($newsPost->academicYear)
                                <span class="rounded-full bg-white/20 px-3 py-1.5 text-sm font-semibold text-white backdrop-blur-sm">
                                    {{ $newsPost->academicYear->year }}
                                </span>
                            @endif
                            @if($newsPost->tags->isNotEmpty())
                                @foreach($newsPost->tags->take(3) as $tag)
                                    <span class="rounded-full bg-white/20 px-3 py-1.5 text-sm font-medium text-white backdrop-blur-sm">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                        
                        <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                            {{ $newsPost->title }}
                        </h1>
                        
                        {{-- Meta Information --}}
                        <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-white/80">
                            @if($newsPost->published_at)
                                <time datetime="{{ $newsPost->published_at->toIso8601String() }}" class="inline-flex items-center gap-2">
                                    <flux:icon name="calendar" class="[:where(&)]:size-4" variant="outline" />
                                    {{ $newsPost->published_at->translatedFormat('d F Y') }}
                                </time>
                            @endif
                            @if($newsPost->author)
                                <span class="inline-flex items-center gap-2">
                                    <flux:icon name="user" class="[:where(&)]:size-4" variant="outline" />
                                    {{ $newsPost->author->name }}
                                </span>
                            @endif
                            @if($newsPost->city || $newsPost->country)
                                <span class="inline-flex items-center gap-2">
                                    <flux:icon name="map-pin" class="[:where(&)]:size-4" variant="outline" />
                                    {{ collect([$newsPost->city, $newsPost->country])->filter()->implode(', ') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Icon decoration --}}
                    <div class="hidden lg:block">
                        <div class="rounded-2xl bg-white/10 p-6 backdrop-blur-sm">
                            <flux:icon name="newspaper" class="[:where(&)]:size-20 text-white/80" variant="outline" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Content Section --}}
    <x-ui.section>
        <div class="mx-auto max-w-4xl">
            {{-- Excerpt --}}
            @if($newsPost->excerpt)
                <div class="mb-8 rounded-xl border-l-4 border-erasmus-500 bg-erasmus-50 p-6 dark:border-erasmus-400 dark:bg-erasmus-900/20">
                    <p class="text-lg font-medium leading-relaxed text-zinc-700 dark:text-zinc-300">
                        {{ $newsPost->excerpt }}
                    </p>
                </div>
            @endif

            {{-- Main Content --}}
            @if($newsPost->content)
                <div class="prose prose-lg prose-zinc max-w-none dark:prose-invert prose-headings:font-bold prose-headings:text-zinc-900 dark:prose-headings:text-white prose-p:text-zinc-600 dark:prose-p:text-zinc-400 prose-a:text-erasmus-600 dark:prose-a:text-erasmus-400 prose-a:no-underline hover:prose-a:underline prose-strong:text-zinc-900 dark:prose-strong:text-white">
                    {!! nl2br(e($newsPost->content)) !!}
                </div>
            @endif

            {{-- Additional Information Cards --}}
            @if($newsPost->city || $newsPost->country || $newsPost->host_entity || $newsPost->mobility_type || $newsPost->mobility_category)
                <div class="mt-12 grid gap-6 sm:grid-cols-2">
                    @if($newsPost->city || $newsPost->country)
                        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-erasmus-50 p-3 dark:bg-erasmus-900/20">
                                    <flux:icon name="map-pin" class="[:where(&)]:size-6 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('common.news.location') }}</p>
                                    <p class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                        {{ collect([$newsPost->city, $newsPost->country])->filter()->implode(', ') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($newsPost->host_entity)
                        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-erasmus-50 p-3 dark:bg-erasmus-900/20">
                                    <flux:icon name="building-office" class="[:where(&)]:size-6 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('common.news.host_entity') }}</p>
                                    <p class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                        {{ $newsPost->host_entity }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($newsPost->mobility_type)
                        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-erasmus-50 p-3 dark:bg-erasmus-900/20">
                                    <flux:icon name="user-group" class="[:where(&)]:size-6 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('common.news.mobility_type') }}</p>
                                    <p class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                        {{ $newsPost->mobility_type === 'alumnado' ? __('common.news.students') : __('common.news.staff') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($newsPost->mobility_category)
                        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-erasmus-50 p-3 dark:bg-erasmus-900/20">
                                    <flux:icon name="academic-cap" class="[:where(&)]:size-6 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('common.news.category') }}</p>
                                    <p class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                        @php
                                            $categories = [
                                                'FCT' => __('common.news.fct'),
                                                'job_shadowing' => __('common.news.job_shadowing'),
                                                'intercambio' => __('common.news.exchange'),
                                                'curso' => __('common.news.course'),
                                                'otro' => __('common.news.other'),
                                            ];
                                        @endphp
                                        {{ $categories[$newsPost->mobility_category] ?? $newsPost->mobility_category }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Tags --}}
            @if($newsPost->tags->isNotEmpty())
                <div class="mt-8">
                    <h3 class="mb-4 flex items-center gap-3 text-xl font-bold text-zinc-900 dark:text-white">
                        <span class="inline-flex items-center justify-center rounded-lg bg-erasmus-50 p-2 dark:bg-erasmus-900/20">
                            <flux:icon name="tag" class="[:where(&)]:size-6 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                        </span>
                        {{ __('common.news.tags') }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($newsPost->tags as $tag)
                            <x-ui.badge size="md" color="primary">
                                {{ $tag->name }}
                            </x-ui.badge>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-ui.section>

    {{-- Related News Section --}}
    @if($this->relatedNews->isNotEmpty())
        <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
            <x-slot:title>{{ __('common.news.related_news') }}</x-slot:title>
            <x-slot:description>{{ __('common.news.discover_related') }}</x-slot:description>
            <x-slot:actions>
                <x-ui.button href="{{ route('noticias.index') }}" variant="outline" icon="arrow-right" navigate>
                    {{ __('common.news.view_all') }}
                </x-ui.button>
            </x-slot:actions>
            
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->relatedNews as $relatedNewsPost)
                    @php
                        // Use thumbnail conversion for related news cards (WebP optimized)
                        $relatedImage = $relatedNewsPost->getFirstMediaUrl('featured', 'thumbnail')
                            ?: $relatedNewsPost->getFirstMediaUrl('featured');
                    @endphp
                    <x-content.news-card 
                        :news="$relatedNewsPost" 
                        :imageUrl="$relatedImage"
                        :showProgram="true"
                        :showAuthor="false"
                        :showDate="true"
                    />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- Related Calls Section --}}
    @if($this->relatedCalls->isNotEmpty())
        <x-ui.section>
            <x-slot:title>{{ __('common.news.related_calls') }}</x-slot:title>
            <x-slot:description>{{ __('common.news.check_related_calls') }}</x-slot:description>
            <x-slot:actions>
                <x-ui.button href="{{ route('convocatorias.index') }}" variant="outline" icon="arrow-right" navigate>
                    {{ __('common.news.view_all') }}
                </x-ui.button>
            </x-slot:actions>
            
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach($this->relatedCalls as $relatedCall)
                    <x-content.call-card :call="$relatedCall" :showProgram="false" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r from-erasmus-600 to-erasmus-700">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    {{ __('common.news.interested') }}
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-white/80">
                    {{ __('common.news.check_more') }}
                </p>
                <div class="mt-8 flex flex-col justify-center gap-4 sm:flex-row">
                    <x-ui.button 
                        href="{{ route('noticias.index') }}" 
                        variant="secondary"
                        size="lg"
                        navigate
                    >
                        {{ __('common.news.view_all_news') }}
                    </x-ui.button>
                    @if($newsPost->program)
                        <x-ui.button 
                            href="{{ route('programas.show', $newsPost->program) }}" 
                            variant="ghost"
                            size="lg"
                            class="text-white hover:bg-white/10"
                            navigate
                        >
                            {{ __('common.news.view_program') }}
                        </x-ui.button>
                    @endif
                    <x-ui.button 
                        href="{{ route('convocatorias.index') }}" 
                        variant="ghost"
                        size="lg"
                        class="text-white hover:bg-white/10"
                        navigate
                    >
                        {{ __('common.news.view_calls') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</div>
