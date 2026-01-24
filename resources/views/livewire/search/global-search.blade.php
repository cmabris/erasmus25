<div>
    {{-- Header Section --}}
    <section class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white sm:text-4xl">
                    {{ __('common.search.global_title') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('common.search.global_description') }}
                </p>
            </div>

            {{-- Search Input --}}
            <div class="mt-6">
                <x-ui.search-input 
                    name="query"
                    wire:model.live.debounce.300ms="query" 
                    :placeholder="__('common.search.global_placeholder')"
                    size="lg"
                />
            </div>

            {{-- Filters Toggle --}}
            <div class="mt-4 flex items-center justify-between">
                <button
                    type="button"
                    wire:click="toggleFilters"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm transition-colors hover:bg-zinc-50 focus:border-erasmus-500 focus:ring-erasmus-500 focus:ring-1 focus:outline-none dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:hover:bg-zinc-600"
                >
                    <flux:icon 
                        :name="$showFilters ? 'chevron-up' : 'chevron-down'" 
                        class="size-4" 
                        variant="outline" 
                    />
                    {{ __('common.search.advanced_filters') }}
                </button>

                @if($query)
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        {{ __('common.search.clear_search') }}
                    </button>
                @endif
            </div>

            {{-- Advanced Filters Panel --}}
            @if($showFilters)
                <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {{-- Content Types --}}
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ __('common.search.content_types') }}
                            </label>
                            <div class="flex flex-wrap gap-3">
                                @foreach(['programs' => __('common.search.programs'), 'calls' => __('common.search.calls'), 'news' => __('common.search.news'), 'documents' => __('common.search.documents')] as $type => $label)
                                    <label class="inline-flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            wire:model.live="types"
                                            value="{{ $type }}"
                                            class="rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                                        />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Program Filter --}}
                        @if($this->availablePrograms->isNotEmpty())
                            <div>
                                <label for="program-filter" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('common.programs.title') }}
                                </label>
                                <select
                                    id="program-filter"
                                    wire:model.live="program"
                                    class="w-full rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                                >
                                    <option value="">{{ __('common.search.all_programs') }}</option>
                                    @foreach($this->availablePrograms as $prog)
                                        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Academic Year Filter --}}
                        @if($this->availableAcademicYears->isNotEmpty())
                            <div>
                                <label for="academic-year-filter" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('common.nav.academic_years') }}
                                </label>
                                <select
                                    id="academic-year-filter"
                                    wire:model.live="academicYear"
                                    class="w-full rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                                >
                                    <option value="">{{ __('common.search.all_years') }}</option>
                                    @foreach($this->availableAcademicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Results Section --}}
    @if($query)
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if($this->hasResults)
                {{-- Results Summary --}}
                <div class="mb-6">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('common.search.results_found', ['total' => $this->totalResults]) }}
                    </p>
                </div>

                {{-- Results by Type --}}
                <div class="space-y-8">
                    {{-- Programs Results --}}
                    @if(isset($this->results['programs']) && $this->results['programs']->isNotEmpty())
                        <div>
                            <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">
                                {{ __('common.search.programs') }}
                                <span class="ml-2 text-sm font-normal text-zinc-500 dark:text-zinc-400">
                                    ({{ $this->results['programs']->count() }})
                                </span>
                            </h2>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($this->results['programs'] as $program)
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                        <h3 class="font-semibold text-zinc-900 dark:text-white">
                                            <a 
                                                href="{{ $this->getProgramRoute($program) }}"
                                                class="hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                                wire:navigate
                                            >
                                                {{ $program->name }}
                                            </a>
                                        </h3>
                                        @if($program->code)
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $program->code }}
                                            </p>
                                        @endif
                                        @if($program->description)
                                            <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ \Illuminate\Support\Str::limit($program->description, 100) }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Calls Results --}}
                    @if(isset($this->results['calls']) && $this->results['calls']->isNotEmpty())
                        <div>
                            <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">
                                {{ __('common.search.calls') }}
                                <span class="ml-2 text-sm font-normal text-zinc-500 dark:text-zinc-400">
                                    ({{ $this->results['calls']->count() }})
                                </span>
                            </h2>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($this->results['calls'] as $call)
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                        <h3 class="font-semibold text-zinc-900 dark:text-white">
                                            <a 
                                                href="{{ $this->getCallRoute($call) }}"
                                                class="hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                                wire:navigate
                                            >
                                                {{ $call->title }}
                                            </a>
                                        </h3>
                                        <div class="mt-2 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                            @if($call->program)
                                                <span>{{ $call->program->name }}</span>
                                            @endif
                                            @if($call->status === 'abierta')
                                                <flux:badge variant="success" size="sm">{{ __('common.calls.open') }}</flux:badge>
                                            @else
                                                <flux:badge variant="gray" size="sm">{{ __('common.calls.closed') }}</flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- News Results --}}
                    @if(isset($this->results['news']) && $this->results['news']->isNotEmpty())
                        <div>
                            <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">
                                {{ __('common.search.news') }}
                                <span class="ml-2 text-sm font-normal text-zinc-500 dark:text-zinc-400">
                                    ({{ $this->results['news']->count() }})
                                </span>
                            </h2>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($this->results['news'] as $news)
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                        <h3 class="font-semibold text-zinc-900 dark:text-white">
                                            <a 
                                                href="{{ $this->getNewsRoute($news) }}"
                                                class="hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                                wire:navigate
                                            >
                                                {{ $news->title }}
                                            </a>
                                        </h3>
                                        @if($news->excerpt)
                                            <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $news->excerpt }}
                                            </p>
                                        @endif
                                        @if($news->published_at)
                                            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $news->published_at->format('d/m/Y') }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Documents Results --}}
                    @if(isset($this->results['documents']) && $this->results['documents']->isNotEmpty())
                        <div>
                            <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">
                                {{ __('common.search.documents') }}
                                <span class="ml-2 text-sm font-normal text-zinc-500 dark:text-zinc-400">
                                    ({{ $this->results['documents']->count() }})
                                </span>
                            </h2>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($this->results['documents'] as $document)
                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                        <h3 class="font-semibold text-zinc-900 dark:text-white">
                                            <a 
                                                href="{{ $this->getDocumentRoute($document) }}"
                                                class="hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                                wire:navigate
                                            >
                                                {{ $document->title }}
                                            </a>
                                        </h3>
                                        @if($document->category)
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $document->category->name }}
                                            </p>
                                        @endif
                                        @if($document->description)
                                            <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ \Illuminate\Support\Str::limit($document->description, 100) }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Empty State --}}
                <div class="py-12 text-center">
                    <flux:icon name="magnifying-glass" class="mx-auto size-12 text-zinc-400 dark:text-zinc-500" variant="outline" />
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ __('common.search.no_results') }}
                    </h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('common.search.no_results_message') }}
                    </p>
                </div>
            @endif
        </section>
    @else
        {{-- Initial State --}}
        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center">
                <flux:icon name="magnifying-glass" class="mx-auto size-16 text-zinc-400 dark:text-zinc-500" variant="outline" />
                <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('common.search.start_search') }}
                </h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('common.search.start_search_message') }}
                </p>
            </div>
        </section>
    @endif
</div>
