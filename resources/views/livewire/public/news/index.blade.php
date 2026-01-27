<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="news-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#news-pattern)" width="100%" height="100%" />
            </svg>
        </div>
        
        {{-- Decorative elements --}}
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-gold-500/10 blur-3xl"></div>
        
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            {{-- Breadcrumbs --}}
            <div class="mb-8">
                <x-ui.breadcrumbs 
                    :items="[
                        ['label' => __('common.nav.news'), 'href' => route('noticias.index')],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <flux:icon name="newspaper" class="[:where(&)]:size-5" variant="outline" />
                    {{ __('common.news.title') }}
                </div>
                
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    {{ __('common.nav.news') }}
                </h1>
                
                <p class="mt-4 text-lg leading-relaxed text-erasmus-100 sm:text-xl">
                    {{ __('common.news.hero_description') }}
                </p>
            </div>
            
            {{-- Stats Row --}}
            <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6">
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['total'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('Noticias') }}</div>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['this_month'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('Este mes') }}</div>
                </div>
                <div class="col-span-2 rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:col-span-1 sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['this_year'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('Este año') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Filters Section --}}
    <section class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Search --}}
                <div class="w-full sm:max-w-xs">
                    <x-ui.search-input 
                        name="search"
                        wire:model.live.debounce.300ms="search" 
                        :placeholder="__('common.search.news_placeholder')"
                        size="md"
                    />
                </div>
                
                {{-- Filters --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Program Filter --}}
                    @if($this->availablePrograms->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <label for="program-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('Programa:') }}
                            </label>
                            <select 
                                id="program-filter"
                                wire:model.live="program"
                                class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                @foreach($this->availablePrograms as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    {{-- Academic Year Filter --}}
                    @if($this->availableAcademicYears->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <label for="year-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('common.news.year') }}
                            </label>
                            <select 
                                id="year-filter"
                                wire:model.live="academicYear"
                                class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                            >
                                <option value="">{{ __('common.filters.all') }}</option>
                                @foreach($this->availableAcademicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->year }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    {{-- Reset Filters --}}
                    @if($search || $program || $academicYear || $tags)
                        <button 
                            wire:click="resetFilters"
                            type="button"
                            data-test="news-reset-filters"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="x-mark" class="[:where(&)]:size-4" variant="outline" />
                            {{ __('common.actions.reset') }}
                        </button>
                    @endif
                </div>
            </div>
            
            {{-- Tags Filter --}}
            @if($this->availableTags->isNotEmpty())
                <div class="mt-4">
                    <label class="mb-2 block text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ __('common.news.filter_tags') }}
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->availableTags as $tag)
                            @php
                                $isSelected = in_array($tag->id, $this->selectedTagIds, true);
                            @endphp
                            <button
                                type="button"
                                wire:click="toggleTag({{ $tag->id }})"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition-all {{ $isSelected 
                                    ? 'bg-erasmus-600 text-white shadow-sm hover:bg-erasmus-700 dark:bg-erasmus-500 dark:hover:bg-erasmus-600' 
                                    : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600' 
                                }}"
                            >
                                @if($isSelected)
                                    <flux:icon name="check" class="[:where(&)]:size-3.5" variant="outline" />
                                @endif
                                {{ $tag->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Active filters summary --}}
            @if($search || $program || $academicYear || $tags)
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('common.news.active_filters') }}</span>
                    @if($search)
                        <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                            "{{ $search }}"
                            <button wire:click="$set('search', '')" class="hover:text-erasmus-900 dark:hover:text-erasmus-300">
                                <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                            </button>
                        </span>
                    @endif
                    @if($program)
                        @php $selectedProgram = $this->availablePrograms->firstWhere('id', $program); @endphp
                        @if($selectedProgram)
                            <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                                {{ $selectedProgram->name }}
                                <button wire:click="$set('program', '')" class="hover:text-erasmus-900 dark:hover:text-erasmus-300">
                                    <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                                </button>
                            </span>
                        @endif
                    @endif
                    @if($academicYear)
                        @php $selectedYear = $this->availableAcademicYears->firstWhere('id', $academicYear); @endphp
                        @if($selectedYear)
                            <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                                {{ $selectedYear->year }}
                                <button wire:click="$set('academicYear', '')" class="hover:text-erasmus-900 dark:hover:text-erasmus-300">
                                    <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                                </button>
                            </span>
                        @endif
                    @endif
                    @foreach($this->selectedTagIds as $tagId)
                        @php $selectedTag = $this->availableTags->firstWhere('id', $tagId); @endphp
                        @if($selectedTag)
                            <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                                {{ $selectedTag->name }}
                                <button wire:click="removeTag({{ $tagId }})" class="hover:text-erasmus-900 dark:hover:text-erasmus-300">
                                    <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                                </button>
                            </span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- News Grid --}}
    <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ trans_choice(':count noticia encontrada|:count noticias encontradas', $this->news->total(), ['count' => $this->news->total()]) }}
            </p>
        </div>
        
        @if($this->news->isEmpty())
            <x-ui.empty-state 
                :title="__('common.news.no_results_title')"
                :description="$search || $program || $academicYear || $tags
                    ? __('common.news.no_results_filtered') 
                    : __('common.news.no_results_empty')"
                icon="newspaper"
            >
                @if($search || $program || $academicYear || $tags)
                    <x-ui.button 
                        wire:click="resetFilters" 
                        variant="outline" 
                        icon="arrow-path"
                        data-test="news-reset-filters"
                    >
                        {{ __('common.filters.clear') }}
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->news as $newsPost)
                    @php
                        // Use optimized WebP conversions: medium for featured card, thumbnail for regular cards
                        $isFeatured = $loop->first && $loop->iteration === 1 && $this->news->currentPage() === 1;
                        $featuredImage = $newsPost->getFirstMediaUrl('featured', $isFeatured ? 'medium' : 'thumbnail') 
                            ?: $newsPost->getFirstMediaUrl('featured');
                    @endphp
                    <x-content.news-card 
                        :news="$newsPost" 
                        :imageUrl="$featuredImage"
                        :variant="$isFeatured ? 'featured' : 'default'"
                        :showProgram="true"
                        :showAuthor="false"
                        :showDate="true"
                    />
                @endforeach
            </div>
            
            {{-- Pagination --}}
            @if($this->news->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $this->news->links() }}
                </div>
            @endif
        @endif
    </x-ui.section>

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r from-gold-500 to-gold-600">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 lg:flex-row">
                <div class="text-center lg:text-left">
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">
                        {{ __('¿Quieres compartir tu experiencia?') }}
                    </h2>
                    <p class="mt-2 text-gold-100">
                        {{ __('common.news.share_experience') }}
                    </p>
                </div>
                <div class="flex flex-shrink-0 gap-3">
                    <x-ui.button 
                        href="{{ route('programas.index') }}" 
                        variant="secondary"
                        navigate
                    >
                        {{ __('common.news.view_programs') }}
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('convocatorias.index') }}" 
                        variant="ghost"
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
