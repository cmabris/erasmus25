<div>
    {{-- Hero Section with Gradient --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="document-detail-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#document-detail-pattern)" width="100%" height="100%" />
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
                        ['label' => __('common.nav.documents'), 'href' => route('documentos.index')],
                        ['label' => $document->title],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    {{-- Badges --}}
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        @if($document->category)
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                                <flux:icon name="folder" class="[:where(&)]:size-5" variant="outline" />
                                {{ $document->category->name }}
                            </div>
                        @endif
                        @if($document->program)
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                                <flux:icon name="academic-cap" class="[:where(&)]:size-5" variant="outline" />
                                {{ $document->program->name }}
                            </div>
                        @endif
                        @if($document->academicYear)
                            <span class="rounded-full bg-white/20 px-3 py-1.5 text-sm font-semibold text-white backdrop-blur-sm">
                                {{ $document->academicYear->year }}
                            </span>
                        @endif
                        <span class="rounded-full bg-white/20 px-3 py-1.5 text-sm font-medium text-white backdrop-blur-sm">
                            {{ $this->documentTypeConfig['label'] }}
                        </span>
                    </div>
                    
                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                        {{ $document->title }}
                    </h1>
                    
                    {{-- Meta Information --}}
                    <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-white/80">
                        @if($document->created_at)
                            <time datetime="{{ $document->created_at->toIso8601String() }}" class="inline-flex items-center gap-2">
                                <flux:icon name="calendar" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.documents.published_on') }} {{ $document->created_at->translatedFormat('d F Y') }}
                            </time>
                        @endif
                        @if($document->creator)
                            <span class="inline-flex items-center gap-2">
                                <flux:icon name="user" class="[:where(&)]:size-4" variant="outline" />
                                {{ $document->creator->name }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-2">
                            <flux:icon name="arrow-down-tray" class="[:where(&)]:size-4" variant="outline" />
                            {{ number_format($document->download_count) }} {{ __('common.documents.downloads_count') }}
                        </span>
                    </div>
                </div>
                
                {{-- Icon decoration --}}
                <div class="hidden lg:block">
                    <div class="rounded-2xl bg-white/10 p-6 backdrop-blur-sm">
                        <flux:icon :name="$this->documentTypeConfig['icon']" class="[:where(&)]:size-20 text-white/80" variant="outline" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Document Information --}}
    <x-ui.section>
        <div class="mx-auto max-w-4xl">
            {{-- Description --}}
            @if($document->description)
                <div class="mb-8">
                    <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                        {{ __('common.documents.description') }}
                    </h2>
                    <div class="prose prose-zinc max-w-none dark:prose-invert">
                        <p class="text-lg leading-relaxed text-zinc-700 dark:text-zinc-300">
                            {{ $document->description }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- File Information Card --}}
            @if($this->fileUrl)
                <div class="mb-8 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="mb-4 text-xl font-semibold text-zinc-900 dark:text-white">
                        {{ __('common.documents.file_info') }}
                    </h2>
                    
                    <div class="mb-6 space-y-3">
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-700">
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('common.documents.filename') }}</span>
                            <span class="text-sm text-zinc-900 dark:text-white">{{ $this->fileName }}</span>
                        </div>
                        
                        @if($this->fileSize)
                            <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-700">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('common.documents.size') }}</span>
                                <span class="text-sm text-zinc-900 dark:text-white">{{ $this->fileSize }}</span>
                            </div>
                        @endif
                        
                        @if($this->fileMimeType)
                            <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-700">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('common.documents.file_type') }}</span>
                                <span class="text-sm text-zinc-900 dark:text-white">{{ $this->fileMimeType }}</span>
                            </div>
                        @endif
                        
                        @if($document->version)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('common.documents.version') }}</span>
                                <span class="text-sm text-zinc-900 dark:text-white">{{ $document->version }}</span>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Download Button --}}
                    <div class="flex justify-center">
                        <x-ui.button 
                            wire:click="download"
                            size="lg"
                            icon="arrow-down-tray"
                            class="w-full sm:w-auto"
                        >
                            {{ __('common.documents.download_document') }}
                        </x-ui.button>
                    </div>
                </div>
            @else
                <div class="mb-8 rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="text-center">
                        <flux:icon name="exclamation-triangle" class="[:where(&)]:size-12 mx-auto mb-4 text-zinc-400" variant="outline" />
                        <p class="text-zinc-600 dark:text-zinc-400">
                            {{ __('common.documents.no_file') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Media Consent Information --}}
            @if($this->hasMediaConsent)
                <div class="mb-8 rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-900/20">
                    <div class="flex items-start gap-3">
                        <flux:icon name="shield-exclamation" class="[:where(&)]:size-6 mt-0.5 shrink-0 text-amber-600 dark:text-amber-400" variant="outline" />
                        <div class="flex-1">
                            <h3 class="mb-2 font-semibold text-amber-900 dark:text-amber-100">
                                {{ __('common.documents.consent_info') }}
                            </h3>
                            <p class="mb-4 text-sm text-amber-800 dark:text-amber-200">
                                {{ __('common.documents.consent_warning') }}
                            </p>
                            
                            @if($this->mediaConsents->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($this->mediaConsents as $consent)
                                        <div class="rounded-lg border border-amber-200 bg-white p-3 dark:border-amber-800 dark:bg-zinc-800">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                        {{ $consent->person_name }}
                                                    </p>
                                                    @if($consent->consent_date)
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ __('common.documents.consent_given_on') }} {{ $consent->consent_date->translatedFormat('d F Y') }}
                                                        </p>
                                                    @endif
                                                </div>
                                                @if($consent->consent_given)
                                                    <flux:icon name="check-circle" class="[:where(&)]:size-5 text-green-500" variant="outline" />
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Related Documents --}}
            @if($this->relatedDocuments->isNotEmpty())
                <div class="mb-8">
                    <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                        {{ __('common.documents.related_documents') }}
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($this->relatedDocuments as $relatedDoc)
                            <x-content.document-card 
                                :document="$relatedDoc"
                                variant="compact"
                                :showCategory="true"
                                :showProgram="false"
                                :showDownloadCount="true"
                                :showDocumentType="true"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Related Calls --}}
            @if($this->relatedCalls->isNotEmpty())
                <div class="mb-8">
                    <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                        {{ __('common.documents.related_calls') }}
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($this->relatedCalls as $call)
                            <x-content.call-card 
                                :call="$call"
                                variant="compact"
                                :showProgram="false"
                                :showStatus="true"
                            />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-ui.section>

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r from-gold-500 to-gold-600">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 lg:flex-row">
                <div class="text-center lg:text-left">
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">
                        {{ __('common.documents.need_more') }}
                    </h2>
                    <p class="mt-2 text-gold-100">
                        {{ __('common.documents.explore_library') }}
                    </p>
                </div>
                <div class="flex flex-shrink-0 gap-3">
                    <x-ui.button 
                        href="{{ route('documentos.index') }}" 
                        variant="secondary"
                        navigate
                    >
                        {{ __('common.documents.view_all_documents') }}
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('convocatorias.index') }}" 
                        variant="ghost"
                        class="text-white hover:bg-white/10"
                        navigate
                    >
                        {{ __('common.documents.view_calls') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</div>

