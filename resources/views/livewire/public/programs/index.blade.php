<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="programs-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#programs-pattern)" width="100%" height="100%" />
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
                        ['label' => __('common.nav.programs'), 'href' => route('programas.index')],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <flux:icon name="academic-cap" class="[:where(&)]:size-5" variant="outline" />
                    {{ __('common.programs.title') }}
                </div>
                
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    {{ __('common.programs.hero_title') }}
                </h1>
                
                <p class="mt-4 text-lg leading-relaxed text-erasmus-100 sm:text-xl">
                    {{ __('common.programs.hero_description') }}
                </p>
            </div>
            
            {{-- Stats Row --}}
            <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-6">
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['active'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('common.programs.active_programs') }}</div>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['mobility'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('common.programs.mobility') }}</div>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['cooperation'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('common.programs.cooperation') }}</div>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">27+</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('common.programs.countries') }}</div>
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
                        :placeholder="__('common.search.program_placeholder')"
                        size="md"
                    />
                </div>
                
                {{-- Filters --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Type Filter --}}
                    <div class="flex items-center gap-2">
                        <label for="type-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ __('common.programs.type') }}
                        </label>
                        <select 
                            id="type-filter"
                            wire:model.live="type"
                            class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                        >
                            @foreach($this->programTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Active Only Toggle --}}
                    <label class="flex cursor-pointer items-center gap-2">
                        <input 
                            type="checkbox"
                            name="onlyActive"
                            wire:model.live="onlyActive"
                            class="size-4 rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                        >
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('common.programs.active_only') }}
                        </span>
                    </label>
                    
                    {{-- Reset Filters --}}
                    @if($search || $type || !$onlyActive)
                        <button 
                            wire:click="resetFilters"
                            type="button"
                            data-test="programs-reset-filters"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="x-mark" class="[:where(&)]:size-4" variant="outline" />
                            {{ __('common.actions.reset') }}
                        </button>
                    @endif
                </div>
            </div>
            
            {{-- Active filters summary --}}
            @if($search || $type)
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('common.programs.filters') }}</span>
                    @if($search)
                        <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                            "{{ $search }}"
                            <button wire:click="$set('search', '')" class="hover:text-erasmus-900 dark:hover:text-erasmus-100">
                                <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                            </button>
                        </span>
                    @endif
                    @if($type)
                        <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                            {{ $this->programTypes[$type] ?? $type }}
                            <button wire:click="$set('type', '')" class="hover:text-erasmus-900 dark:hover:text-erasmus-100">
                                <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                            </button>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Programs Grid --}}
    <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ trans_choice(':count programa encontrado|:count programas encontrados', $this->programs->total(), ['count' => $this->programs->total()]) }}
            </p>
        </div>
        
        @if($this->programs->isEmpty())
            <x-ui.empty-state 
                :title="__('common.programs.no_results_title')"
                :description="$search || $type 
                    ? __('common.programs.no_results_filtered') 
                    : __('common.programs.no_results_empty')"
                icon="academic-cap"
            >
                @if($search || $type || !$onlyActive)
                    <x-ui.button 
                        wire:click="resetFilters" 
                        variant="outline" 
                        icon="arrow-path"
                        data-test="programs-reset-filters"
                    >
                        {{ __('common.filters.clear') }}
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->programs as $program)
                    <x-content.program-card 
                        :program="$program" 
                        :variant="$loop->first && $loop->iteration === 1 && $this->programs->currentPage() === 1 ? 'featured' : 'default'"
                    />
                @endforeach
            </div>
            
            {{-- Pagination --}}
            @if($this->programs->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $this->programs->links() }}
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
                        {{ __('common.programs.cta_title') }}
                    </h2>
                    <p class="mt-2 text-gold-100">
                        {{ __('common.programs.cta_description') }}
                    </p>
                </div>
                <div class="flex flex-shrink-0 gap-3">
                    <x-ui.button 
                        href="#" 
                        variant="secondary"
                    >
                        {{ __('common.programs.view_calls') }}
                    </x-ui.button>
                    <x-ui.button 
                        href="#" 
                        variant="ghost"
                        class="text-white hover:bg-white/10"
                    >
                        {{ __('common.programs.contact') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</div>
