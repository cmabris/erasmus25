@props([
    'document' => null,
    'title' => null,
    'slug' => null,
    'description' => null,
    'category' => null,
    'program' => null,
    'academicYear' => null,
    'documentType' => null,
    'downloadCount' => 0,
    'createdAt' => null,
    'updatedAt' => null,
    'href' => null,
    'variant' => 'default', // default, compact, featured, horizontal
    'showCategory' => true,
    'showProgram' => true,
    'showDownloadCount' => true,
    'showDocumentType' => true,
])

@php
    // Capture variant and unset to prevent propagation to child components
    $cardVariant = $variant;
    unset($variant);
    
    // Extract from model if provided
    $title = $document?->title ?? $title;
    $slug = $document?->slug ?? $slug;
    $description = $document?->description ?? $description;
    $category = $document?->category ?? $category;
    $program = $document?->program ?? $program;
    $academicYear = $document?->academicYear ?? $academicYear;
    $documentType = $document?->document_type ?? $documentType;
    $downloadCount = $document?->download_count ?? $downloadCount;
    $createdAt = $document?->created_at ?? $createdAt;
    $updatedAt = $document?->updated_at ?? $updatedAt;
    
    // Generate href - use route if available, otherwise fallback
    if ($href) {
        // href already provided
    } elseif ($slug && $document) {
        // Try to use documentos.show route if available
        try {
            $href = route('documentos.show', $document);
        } catch (\Illuminate\Routing\Exceptions\RouteNotFoundException $e) {
            $href = null; // Route not yet defined
        }
    } else {
        $href = null;
    }
    
    // Format dates
    $dateFormatted = $createdAt ? \Carbon\Carbon::parse($createdAt)->translatedFormat('d M Y') : null;
    $dateRelative = $createdAt ? \Carbon\Carbon::parse($createdAt)->diffForHumans() : null;
    
    // Document type configuration
    $documentTypeConfig = match($documentType) {
        'convocatoria' => ['icon' => 'document-text', 'color' => 'primary', 'label' => __('Convocatoria')],
        'modelo' => ['icon' => 'document-duplicate', 'color' => 'info', 'label' => __('Modelo')],
        'seguro' => ['icon' => 'shield-check', 'color' => 'success', 'label' => __('Seguro')],
        'consentimiento' => ['icon' => 'clipboard-document-check', 'color' => 'warning', 'label' => __('Consentimiento')],
        'guia' => ['icon' => 'book-open', 'color' => 'info', 'label' => __('Guía')],
        'faq' => ['icon' => 'question-mark-circle', 'color' => 'info', 'label' => __('FAQ')],
        'otro' => ['icon' => 'document', 'color' => 'neutral', 'label' => __('Otro')],
        default => ['icon' => 'document', 'color' => 'neutral', 'label' => __('Documento')],
    };
    
    // Default icon for document card
    $defaultIcon = $documentTypeConfig['icon'];
@endphp

@if($cardVariant === 'featured')
    {{-- Featured variant - large card with icon --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="none"
        {{ $attributes->except(['variant', 'document', 'showCategory', 'showProgram', 'showDownloadCount', 'showDocumentType'])->class(['overflow-hidden']) }}
    >
        {{-- Icon Header --}}
        <div class="flex aspect-[16/9] w-full items-center justify-center bg-gradient-to-br from-erasmus-100 to-erasmus-200 dark:from-erasmus-900/50 dark:to-erasmus-800/50">
            <div class="rounded-full bg-white/80 p-6 dark:bg-zinc-800/80">
                <flux:icon :name="$defaultIcon" class="[:where(&)]:size-16 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
            </div>
        </div>
        
        <div class="p-6">
            {{-- Meta --}}
            <div class="mb-3 flex flex-wrap items-center gap-2">
                @if($showCategory && $category)
                    <x-ui.badge size="sm" color="primary">{{ $category->name ?? $category }}</x-ui.badge>
                @endif
                @if($showDocumentType && $documentType)
                    <x-ui.badge size="sm" :color="$documentTypeConfig['color']">{{ $documentTypeConfig['label'] }}</x-ui.badge>
                @endif
                @if($showProgram && $program)
                    <x-ui.badge size="sm" color="secondary">{{ $program->name ?? $program }}</x-ui.badge>
                @endif
            </div>
            
            {{-- Title --}}
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
                {{ $title }}
            </h3>
            
            {{-- Description --}}
            @if($description)
                <p class="mt-3 line-clamp-3 text-zinc-600 dark:text-zinc-400">
                    {{ $description }}
                </p>
            @endif
            
            {{-- Footer --}}
            <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-700">
                <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($showDownloadCount)
                        <span class="inline-flex items-center gap-1">
                            <flux:icon name="arrow-down-tray" class="[:where(&)]:size-4" variant="outline" />
                            {{ number_format($downloadCount) }}
                        </span>
                    @endif
                    @if($dateFormatted)
                        <time datetime="{{ $createdAt }}">{{ $dateFormatted }}</time>
                    @endif
                </div>
                
                <span class="inline-flex items-center gap-1 text-sm font-medium text-erasmus-600 dark:text-erasmus-400">
                    {{ __('Ver documento') }}
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
        {{ $attributes->except(['variant', 'document', 'showCategory', 'showProgram', 'showDownloadCount', 'showDocumentType'])->class(['overflow-hidden']) }}
    >
        <div class="flex flex-col sm:flex-row">
            {{-- Icon --}}
            <div class="shrink-0 sm:w-48 md:w-56">
                <div class="flex aspect-[16/9] h-full w-full items-center justify-center bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700 sm:aspect-auto">
                    <flux:icon :name="$defaultIcon" class="[:where(&)]:size-10 text-zinc-400" variant="outline" />
                </div>
            </div>
            
            {{-- Content --}}
            <div class="flex flex-1 flex-col justify-between p-4 sm:p-5">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        @if($showCategory && $category)
                            <x-ui.badge size="sm" color="primary">{{ $category->name ?? $category }}</x-ui.badge>
                        @endif
                        @if($showDocumentType && $documentType)
                            <x-ui.badge size="sm" :color="$documentTypeConfig['color']">{{ $documentTypeConfig['label'] }}</x-ui.badge>
                        @endif
                    </div>
                    
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
                    
                    @if($description)
                        <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $description }}
                        </p>
                    @endif
                </div>
                
                <div class="mt-3 flex items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($showDownloadCount)
                        <span class="inline-flex items-center gap-1">
                            <flux:icon name="arrow-down-tray" class="[:where(&)]:size-3.5" variant="outline" />
                            {{ number_format($downloadCount) }}
                        </span>
                    @endif
                    @if($dateRelative)
                        <time datetime="{{ $createdAt }}">{{ $dateRelative }}</time>
                    @endif
                    @if($showProgram && $program)
                        <span class="inline-flex items-center gap-1">
                            <flux:icon name="academic-cap" class="[:where(&)]:size-3.5" variant="outline" />
                            {{ $program->name ?? $program }}
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
        {{ $attributes->except(['variant', 'document', 'showCategory', 'showProgram', 'showDownloadCount', 'showDocumentType']) }}
    >
        <div class="flex items-start gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-erasmus-100 to-erasmus-200 dark:from-erasmus-800 dark:to-erasmus-700">
                <flux:icon :name="$defaultIcon" class="[:where(&)]:size-6 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="truncate font-medium text-zinc-900 dark:text-white">{{ $title }}</h3>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($showCategory && $category)
                        <span>{{ $category->name ?? $category }}</span>
                    @endif
                    @if($showDownloadCount)
                        <span class="inline-flex items-center gap-1">
                            <flux:icon name="arrow-down-tray" class="[:where(&)]:size-3" variant="outline" />
                            {{ number_format($downloadCount) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-ui.card>

@else
    {{-- Default variant --}}
    <x-ui.card 
        :href="$href" 
        :hover="true" 
        padding="none"
        {{ $attributes->except(['variant', 'document', 'showCategory', 'showProgram', 'showDownloadCount', 'showDocumentType'])->class(['overflow-hidden']) }}
    >
        {{-- Icon Header --}}
        <div class="flex aspect-[16/10] w-full items-center justify-center bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700">
            <div class="rounded-full bg-white/80 p-4 dark:bg-zinc-700/80">
                <flux:icon :name="$defaultIcon" class="[:where(&)]:size-12 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
            </div>
        </div>
        
        <div class="p-4 sm:p-5">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                @if($showCategory && $category)
                    <x-ui.badge size="sm" color="primary">{{ $category->name ?? $category }}</x-ui.badge>
                @endif
                @if($showDocumentType && $documentType)
                    <x-ui.badge size="sm" :color="$documentTypeConfig['color']">{{ $documentTypeConfig['label'] }}</x-ui.badge>
                @endif
            </div>
            
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
            
            @if($description)
                <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ $description }}
                </p>
            @endif
            
            <div class="mt-3 flex items-center justify-between text-sm text-zinc-500 dark:text-zinc-400">
                @if($showDownloadCount)
                    <span class="inline-flex items-center gap-1">
                        <flux:icon name="arrow-down-tray" class="[:where(&)]:size-4" variant="outline" />
                        {{ number_format($downloadCount) }}
                    </span>
                @endif
                @if($dateRelative)
                    <time datetime="{{ $createdAt }}">{{ $dateRelative }}</time>
                @endif
            </div>
        </div>
    </x-ui.card>
@endif

