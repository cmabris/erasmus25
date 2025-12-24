<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br {{ $this->programConfig['gradientDark'] }}">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="program-detail-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#program-detail-pattern)" width="100%" height="100%" />
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
                        ['label' => __('Programas'), 'href' => route('programas.index')],
                        ['label' => $program->name],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    {{-- Program badge --}}
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                            <flux:icon :name="$this->programConfig['icon']" class="[:where(&)]:size-5" variant="outline" />
                            {{ $this->programConfig['type'] }}
                        </div>
                        <span class="rounded-full bg-white/20 px-3 py-1.5 text-sm font-semibold text-white backdrop-blur-sm">
                            {{ $program->code }}
                        </span>
                        @if($program->is_active)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1.5 text-sm font-medium text-emerald-100 backdrop-blur-sm">
                                <span class="size-2 animate-pulse rounded-full bg-emerald-400"></span>
                                {{ __('Activo') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-500/20 px-3 py-1.5 text-sm font-medium text-zinc-200 backdrop-blur-sm">
                                {{ __('Inactivo') }}
                            </span>
                        @endif
                    </div>
                    
                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                        {{ $program->name }}
                    </h1>
                </div>
                
                {{-- Icon decoration --}}
                <div class="hidden lg:block">
                    <div class="rounded-2xl bg-white/10 p-6 backdrop-blur-sm">
                        <flux:icon :name="$this->programConfig['icon']" class="[:where(&)]:size-20 text-white/80" variant="outline" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Program Description --}}
    <x-ui.section>
        <div class="mx-auto max-w-4xl">
            <div class="prose prose-lg prose-zinc max-w-none dark:prose-invert">
                <h2 class="flex items-center gap-3 text-2xl font-bold text-zinc-900 dark:text-white">
                    <span class="inline-flex items-center justify-center rounded-lg {{ $this->programConfig['bgLight'] }} p-2">
                        <flux:icon name="information-circle" class="[:where(&)]:size-6 {{ $this->programConfig['textColor'] }}" variant="outline" />
                    </span>
                    {{ __('common.programs.about') }}
                </h2>
                
                <p class="mt-4 whitespace-pre-line text-lg leading-relaxed text-zinc-600 dark:text-zinc-400">
                    {{ $program->description }}
                </p>
            </div>
            
            {{-- Key Info Cards --}}
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg {{ $this->programConfig['bgLight'] }} p-2">
                            <flux:icon name="globe-europe-africa" class="[:where(&)]:size-5 {{ $this->programConfig['textColor'] }}" variant="outline" />
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Ámbito') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Unión Europea') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg {{ $this->programConfig['bgLight'] }} p-2">
                            <flux:icon name="user-group" class="[:where(&)]:size-5 {{ $this->programConfig['textColor'] }}" variant="outline" />
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Destinatarios') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                @if(str_contains($program->code, 'VET'))
                                    {{ __('Estudiantes y profesorado FP') }}
                                @elseif(str_contains($program->code, 'HED'))
                                    {{ __('Universitarios y personal') }}
                                @elseif(str_contains($program->code, 'SCH'))
                                    {{ __('Centros educativos') }}
                                @elseif(str_contains($program->code, 'ADU'))
                                    {{ __('Educación de adultos') }}
                                @else
                                    {{ __('Comunidad educativa') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800 sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg {{ $this->programConfig['bgLight'] }} p-2">
                            <flux:icon name="calendar-days" class="[:where(&)]:size-5 {{ $this->programConfig['textColor'] }}" variant="outline" />
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Convocatorias') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $this->relatedCalls->count() }} {{ __('disponibles') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.section>

    {{-- Related Calls Section --}}
    @if($this->relatedCalls->isNotEmpty())
        <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
            <x-slot:title>{{ __('Convocatorias de este programa') }}</x-slot:title>
            <x-slot:description>{{ __('Consulta las convocatorias disponibles y solicita tu participación.') }}</x-slot:description>
            <x-slot:actions>
                <x-ui.button href="#" variant="outline" icon="arrow-right">
                    {{ __('Ver todas') }}
                </x-ui.button>
            </x-slot:actions>
            
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach($this->relatedCalls as $call)
                    <x-content.call-card :call="$call" :showProgram="false" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- Related News Section --}}
    @if($this->relatedNews->isNotEmpty())
        <x-ui.section>
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

    {{-- Empty state for no content --}}
    @if($this->relatedCalls->isEmpty() && $this->relatedNews->isEmpty())
        <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
            <x-ui.empty-state 
                :title="__('Contenido próximamente')"
                :description="__('Actualmente no hay convocatorias ni noticias para este programa. Te notificaremos cuando haya novedades.')"
                icon="clock"
            >
                <x-ui.button href="{{ route('programas.index') }}" variant="outline" icon="arrow-left" navigate>
                    {{ __('Volver a programas') }}
                </x-ui.button>
            </x-ui.empty-state>
        </x-ui.section>
    @endif

    {{-- Other Programs Section --}}
    @if($this->otherPrograms->isNotEmpty())
        <x-ui.section class="{{ $this->relatedCalls->isEmpty() && $this->relatedNews->isEmpty() ? '' : 'bg-zinc-50 dark:bg-zinc-900' }}">
            <x-slot:title>{{ __('Otros programas que te pueden interesar') }}</x-slot:title>
            <x-slot:actions>
                <x-ui.button href="{{ route('programas.index') }}" variant="outline" icon="arrow-right" navigate>
                    {{ __('Ver todos') }}
                </x-ui.button>
            </x-slot:actions>
            
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->otherPrograms as $otherProgram)
                    <x-content.program-card :program="$otherProgram" variant="compact" />
                @endforeach
            </div>
        </x-ui.section>
    @endif

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r {{ $this->programConfig['gradient'] }}">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    {{ __('¿Listo para participar en') }} {{ $program->name }}?
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-white/80">
                    {{ __('Consulta las convocatorias activas o contacta con nosotros para resolver tus dudas.') }}
                </p>
                <div class="mt-8 flex flex-col justify-center gap-4 sm:flex-row">
                    <x-ui.button 
                        href="#" 
                        variant="secondary"
                        size="lg"
                    >
                        {{ __('Ver convocatorias') }}
                    </x-ui.button>
                    <x-ui.button 
                        href="{{ route('programas.index') }}" 
                        variant="ghost"
                        size="lg"
                        class="text-white hover:bg-white/10"
                        navigate
                    >
                        {{ __('Explorar más programas') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</div>
