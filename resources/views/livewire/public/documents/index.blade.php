<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="documents-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#documents-pattern)" width="100%" height="100%" />
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
                        ['label' => __('Documentos'), 'href' => route('documentos.index')],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <flux:icon name="folder-open" class="[:where(&)]:size-5" variant="outline" />
                    {{ __('Documentos Erasmus+') }}
                </div>
                
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    {{ __('Documentos y Recursos') }}
                </h1>
                
                <p class="mt-4 text-lg leading-relaxed text-erasmus-100 sm:text-xl">
                    {{ __('Accede a todos los documentos oficiales, guías, modelos y formularios relacionados con los programas Erasmus+. Descarga los recursos que necesites para tu movilidad internacional.') }}
                </p>
            </div>
            
            {{-- Stats Row --}}
            <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6">
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['total'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('Documentos') }}</div>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ $this->stats['categories'] }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('Categorías') }}</div>
                </div>
                <div class="col-span-2 rounded-xl bg-white/10 px-4 py-4 text-center backdrop-blur-sm sm:col-span-1 sm:px-6">
                    <div class="text-2xl font-bold text-white sm:text-3xl">{{ number_format($this->stats['total_downloads']) }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('Descargas') }}</div>
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
                        wire:model.live.debounce.300ms="search" 
                        :placeholder="__('Buscar documento...')"
                        size="md"
                    />
                </div>
                
                {{-- Filters --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Category Filter --}}
                    @if($this->availableCategories->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <label for="category-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('Categoría:') }}
                            </label>
                            <select 
                                id="category-filter"
                                wire:model.live="category"
                                class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                            >
                                <option value="">{{ __('Todas') }}</option>
                                @foreach($this->availableCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
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
                                {{ __('Año:') }}
                            </label>
                            <select 
                                id="year-filter"
                                wire:model.live="academicYear"
                                class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                @foreach($this->availableAcademicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->year }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    {{-- Document Type Filter --}}
                    <div class="flex items-center gap-2">
                        <label for="type-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ __('Tipo:') }}
                        </label>
                        <select 
                            id="type-filter"
                            wire:model.live="documentType"
                            class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                        >
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->availableDocumentTypes as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Reset Filters --}}
                    @if($search || $category || $program || $academicYear || $documentType)
                        <button 
                            wire:click="resetFilters"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="x-mark" class="[:where(&)]:size-4" variant="outline" />
                            {{ __('Limpiar') }}
                        </button>
                    @endif
                </div>
            </div>
            
            {{-- Active filters summary --}}
            @if($search || $category || $program || $academicYear || $documentType)
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Filtros activos:') }}</span>
                    @if($search)
                        <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                            "{{ $search }}"
                            <button wire:click="$set('search', '')" class="hover:text-erasmus-900 dark:hover:text-erasmus-300">
                                <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                            </button>
                        </span>
                    @endif
                    @if($category)
                        @php $selectedCategory = $this->availableCategories->firstWhere('id', $category); @endphp
                        @if($selectedCategory)
                            <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                                {{ $selectedCategory->name }}
                                <button wire:click="$set('category', '')" class="hover:text-erasmus-900 dark:hover:text-erasmus-300">
                                    <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                                </button>
                            </span>
                        @endif
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
                    @if($documentType)
                        @php $selectedTypeLabel = $this->availableDocumentTypes[$documentType] ?? $documentType; @endphp
                        <span class="inline-flex items-center gap-1 rounded-full bg-erasmus-100 px-2.5 py-1 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                            {{ $selectedTypeLabel }}
                            <button wire:click="$set('documentType', '')" class="hover:text-erasmus-900 dark:hover:text-erasmus-300">
                                <flux:icon name="x-mark" class="[:where(&)]:size-3" variant="outline" />
                            </button>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Documents Grid --}}
    <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ trans_choice(':count documento encontrado|:count documentos encontrados', $this->documents->total(), ['count' => $this->documents->total()]) }}
            </p>
        </div>
        
        @if($this->documents->isEmpty())
            <x-ui.empty-state 
                :title="__('No se encontraron documentos')"
                :description="$search || $category || $program || $academicYear || $documentType
                    ? __('No hay documentos que coincidan con los filtros seleccionados. Prueba a modificar los criterios de búsqueda.') 
                    : __('Actualmente no hay documentos disponibles. Vuelve a consultar más adelante.')"
                icon="folder-open"
            >
                @if($search || $category || $program || $academicYear || $documentType)
                    <x-ui.button wire:click="resetFilters" variant="outline" icon="arrow-path">
                        {{ __('Limpiar filtros') }}
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->documents as $document)
                    <x-content.document-card 
                        :document="$document" 
                        :variant="$loop->first && $loop->iteration === 1 && $this->documents->currentPage() === 1 ? 'featured' : 'default'"
                        :showCategory="true"
                        :showProgram="true"
                        :showDownloadCount="true"
                        :showDocumentType="true"
                    />
                @endforeach
            </div>
            
            {{-- Pagination --}}
            @if($this->documents->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $this->documents->links() }}
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
                        {{ __('¿Necesitas ayuda?') }}
                    </h2>
                    <p class="mt-2 text-gold-100">
                        {{ __('Si no encuentras el documento que buscas o necesitas más información, contacta con nosotros.') }}
                    </p>
                </div>
                <div class="flex flex-shrink-0 gap-3">
                    <x-ui.button 
                        href="{{ route('programas.index') }}" 
                        variant="secondary"
                        navigate
                    >
                        {{ __('Ver programas') }}
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('convocatorias.index') }}" 
                        variant="ghost"
                        class="text-white hover:bg-white/10"
                        navigate
                    >
                        {{ __('Ver convocatorias') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</div>

