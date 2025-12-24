<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br {{ $this->callConfig['gradientDark'] }}">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="call-detail-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#call-detail-pattern)" width="100%" height="100%" />
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
                        ['label' => __('Convocatorias'), 'href' => route('convocatorias.index')],
                        ['label' => $call->title],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    {{-- Badges --}}
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        @if($call->program)
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                                <flux:icon name="academic-cap" class="[:where(&)]:size-5" variant="outline" />
                                {{ $call->program->name }}
                            </div>
                        @endif
                        @if($call->academicYear)
                            <span class="rounded-full bg-white/20 px-3 py-1.5 text-sm font-semibold text-white backdrop-blur-sm">
                                {{ $call->academicYear->year }}
                            </span>
                        @endif
                        <x-ui.badge 
                            :color="$this->callConfig['color']" 
                            :icon="$this->callConfig['icon']"
                            size="lg"
                            class="bg-white/20 text-white backdrop-blur-sm"
                        >
                            {{ $this->callConfig['statusLabel'] }}
                        </x-ui.badge>
                    </div>
                    
                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                        {{ $call->title }}
                    </h1>
                </div>
                
                {{-- Icon decoration --}}
                <div class="hidden lg:block">
                    <div class="rounded-2xl bg-white/10 p-6 backdrop-blur-sm">
                        <flux:icon :name="$this->callConfig['icon']" class="[:where(&)]:size-20 text-white/80" variant="outline" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Call Information --}}
    <x-ui.section>
        <div class="mx-auto max-w-4xl">
            {{-- Key Info Cards --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="user-group" class="[:where(&)]:size-5 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Tipo') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $call->type === 'alumnado' ? __('Alumnado') : __('Personal') }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="clock" class="[:where(&)]:size-5 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Modalidad') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $call->modality === 'corta' ? __('Corta duración') : __('Larga duración') }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="users" class="[:where(&)]:size-5 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Plazas') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $call->number_of_places }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="map-pin" class="[:where(&)]:size-5 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Destinos') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ is_array($call->destinations) ? count($call->destinations) : 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Destinations List --}}
            @if(is_array($call->destinations) && count($call->destinations) > 0)
                <div class="mt-8">
                    <h3 class="mb-4 flex items-center gap-3 text-xl font-bold text-zinc-900 dark:text-white">
                        <span class="inline-flex items-center justify-center rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="globe-europe-africa" class="[:where(&)]:size-6 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </span>
                        {{ __('Países de destino') }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($call->destinations as $destination)
                            <x-ui.badge size="md" color="primary">
                                {{ $destination }}
                            </x-ui.badge>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Dates --}}
            @if($call->estimated_start_date || $call->estimated_end_date)
                <div class="mt-8 rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <h3 class="mb-4 flex items-center gap-3 text-xl font-bold text-zinc-900 dark:text-white">
                        <span class="inline-flex items-center justify-center rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="calendar-days" class="[:where(&)]:size-6 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </span>
                        {{ __('Fechas estimadas') }}
                    </h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @if($call->estimated_start_date)
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Fecha de inicio estimada') }}</p>
                                <p class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                    {{ $call->estimated_start_date->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        @endif
                        @if($call->estimated_end_date)
                            <div>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Fecha de fin estimada') }}</p>
                                <p class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                    {{ $call->estimated_end_date->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            {{-- Requirements --}}
            @if($call->requirements)
                <div class="mt-8">
                    <h3 class="mb-4 flex items-center gap-3 text-xl font-bold text-zinc-900 dark:text-white">
                        <span class="inline-flex items-center justify-center rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="document-check" class="[:where(&)]:size-6 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </span>
                        {{ __('common.calls.requirements') }}
                    </h3>
                    <div class="prose prose-zinc max-w-none dark:prose-invert">
                        <p class="whitespace-pre-line text-zinc-600 dark:text-zinc-400">
                            {{ $call->requirements }}
                        </p>
                    </div>
                </div>
            @endif
            
            {{-- Documentation --}}
            @if($call->documentation)
                <div class="mt-8">
                    <h3 class="mb-4 flex items-center gap-3 text-xl font-bold text-zinc-900 dark:text-white">
                        <span class="inline-flex items-center justify-center rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="document-duplicate" class="[:where(&)]:size-6 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </span>
                        {{ __('common.calls.documentation') }}
                    </h3>
                    <div class="prose prose-zinc max-w-none dark:prose-invert">
                        <p class="whitespace-pre-line text-zinc-600 dark:text-zinc-400">
                            {{ $call->documentation }}
                        </p>
                    </div>
                </div>
            @endif
            
            {{-- Selection Criteria --}}
            @if($call->selection_criteria)
                <div class="mt-8">
                    <h3 class="mb-4 flex items-center gap-3 text-xl font-bold text-zinc-900 dark:text-white">
                        <span class="inline-flex items-center justify-center rounded-lg {{ $this->callConfig['bgLight'] }} p-2">
                            <flux:icon name="clipboard-document-check" class="[:where(&)]:size-6 {{ $this->callConfig['textColor'] }}" variant="outline" />
                        </span>
                        {{ __('common.calls.selection_criteria') }}
                    </h3>
                    <div class="prose prose-zinc max-w-none dark:prose-invert">
                        <p class="whitespace-pre-line text-zinc-600 dark:text-zinc-400">
                            {{ $call->selection_criteria }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </x-ui.section>

    {{-- Phases Section --}}
    @if($this->currentPhases->isNotEmpty())
        <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
            <x-slot:title>{{ __('common.calls.phases_title') }}</x-slot:title>
            <x-slot:description>{{ __('common.calls.phases_description') }}</x-slot:description>
            
            <x-content.call-phase-timeline :phases="$this->currentPhases" />
        </x-ui.section>
    @endif

    {{-- Resolutions Section --}}
    @if($this->publishedResolutions->isNotEmpty())
        <x-ui.section>
            <x-slot:title>{{ __('common.calls.resolutions_title') }}</x-slot:title>
            <x-slot:description>{{ __('common.calls.resolutions_description') }}</x-slot:description>
            
            <div class="grid gap-6">
                @foreach($this->publishedResolutions as $resolution)
                    <x-content.resolution-card :resolution="$resolution" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- Related News Section --}}
    @if($this->relatedNews->isNotEmpty())
        <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
            <x-slot:title>{{ __('Noticias relacionadas') }}</x-slot:title>
            <x-slot:description>{{ __('Experiencias y novedades del programa.') }}</x-slot:description>
            <x-slot:actions>
                <x-ui.button href="#" variant="outline" icon="arrow-right">
                    {{ __('Ver todas') }}
                </x-ui.button>
            </x-slot:actions>
            
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->relatedNews as $news)
                    <x-content.news-card :news="$news" :showProgram="false" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- Other Calls Section --}}
    @if($this->otherCalls->isNotEmpty())
        <x-ui.section>
            <x-slot:title>{{ __('Otras convocatorias del programa') }}</x-slot:title>
            <x-slot:actions>
                <x-ui.button href="{{ route('convocatorias.index') }}" variant="outline" icon="arrow-right" navigate>
                    {{ __('Ver todas') }}
                </x-ui.button>
            </x-slot:actions>
            
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach($this->otherCalls as $otherCall)
                    <x-content.call-card :call="$otherCall" :showProgram="false" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r {{ $this->callConfig['gradient'] }}">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    {{ __('¿Tienes dudas sobre esta convocatoria?') }}
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-white/80">
                    {{ __('Consulta el programa asociado o contacta con nosotros para resolver tus dudas.') }}
                </p>
                <div class="mt-8 flex flex-col justify-center gap-4 sm:flex-row">
                    @if($call->program)
                        <x-ui.button 
                            href="{{ route('programas.show', $call->program) }}" 
                            variant="secondary"
                            size="lg"
                            navigate
                        >
                            {{ __('Ver programa') }}
                        </x-ui.button>
                    @endif
                    <x-ui.button 
                        href="{{ route('convocatorias.index') }}" 
                        variant="ghost"
                        size="lg"
                        class="text-white hover:bg-white/10"
                        navigate
                    >
                        {{ __('Ver todas las convocatorias') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</div>
