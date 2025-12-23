<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="event-detail-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#event-detail-pattern)" width="100%" height="100%" />
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
                        ['label' => __('Eventos'), 'href' => route('eventos.index')],
                        ['label' => $event->title],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="max-w-3xl">
                {{-- Badges --}}
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    @php
                        $eventTypeConfig = match($event->event_type) {
                            'apertura' => ['icon' => 'play-circle', 'color' => 'success', 'label' => __('Apertura')],
                            'cierre' => ['icon' => 'stop-circle', 'color' => 'danger', 'label' => __('Cierre')],
                            'entrevista' => ['icon' => 'chat-bubble-left-right', 'color' => 'info', 'label' => __('Entrevistas')],
                            'publicacion_provisional' => ['icon' => 'document-text', 'color' => 'warning', 'label' => __('Listado provisional')],
                            'publicacion_definitivo' => ['icon' => 'document-check', 'color' => 'success', 'label' => __('Listado definitivo')],
                            'reunion_informativa' => ['icon' => 'user-group', 'color' => 'primary', 'label' => __('Reunión informativa')],
                            default => ['icon' => 'calendar', 'color' => 'neutral', 'label' => __('Evento')],
                        };
                    @endphp
                    
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                        <flux:icon :name="$eventTypeConfig['icon']" class="[:where(&)]:size-5" variant="outline" />
                        {{ $eventTypeConfig['label'] }}
                    </div>
                    
                    @if($event->program)
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                            <flux:icon name="academic-cap" class="[:where(&)]:size-5" variant="outline" />
                            {{ $event->program->name }}
                        </div>
                    @endif
                    
                    @if($this->isToday)
                        <span class="rounded-full bg-green-500/20 px-3 py-1.5 text-sm font-semibold text-white backdrop-blur-sm">
                            {{ __('Hoy') }}
                        </span>
                    @elseif($this->isUpcoming)
                        <span class="rounded-full bg-blue-500/20 px-3 py-1.5 text-sm font-semibold text-white backdrop-blur-sm">
                            {{ __('Próximo') }}
                        </span>
                    @elseif($this->isPast)
                        <span class="rounded-full bg-zinc-500/20 px-3 py-1.5 text-sm font-semibold text-white backdrop-blur-sm">
                            {{ __('Pasado') }}
                        </span>
                    @endif
                </div>
                
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    {{ $event->title }}
                </h1>
                
                {{-- Date and Location --}}
                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-white/80">
                    <time datetime="{{ $event->start_date->toIso8601String() }}" class="inline-flex items-center gap-2">
                        <flux:icon name="calendar" class="[:where(&)]:size-4" variant="outline" />
                        {{ $event->start_date->translatedFormat('l, d F Y') }}
                        @if($event->start_date->format('H:i') !== '00:00')
                            a las {{ $event->start_date->format('H:i') }}
                        @endif
                        @if($event->end_date)
                            @if($event->start_date->isSameDay($event->end_date))
                                - {{ $event->end_date->format('H:i') }}
                            @else
                                hasta el {{ $event->end_date->translatedFormat('d F Y') }} a las {{ $event->end_date->format('H:i') }}
                            @endif
                        @endif
                    </time>
                    
                    @if($event->location)
                        <span class="inline-flex items-center gap-2">
                            <flux:icon name="map-pin" class="[:where(&)]:size-4" variant="outline" />
                            {{ $event->location }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Content Section --}}
    <x-ui.section>
        <div class="grid gap-8 lg:grid-cols-3">
            {{-- Main Content --}}
            <div class="lg:col-span-2">
                @if($event->description)
                    <div class="prose prose-lg max-w-none dark:prose-invert">
                        <div class="whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">
                            {{ $event->description }}
                        </div>
                    </div>
                @else
                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ __('Este evento no tiene descripción adicional disponible.') }}
                    </p>
                @endif
                
                {{-- Call Information --}}
                @if($event->call)
                    <div class="mt-8 rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ __('Convocatoria relacionada') }}
                        </h3>
                        <p class="mb-4 text-zinc-600 dark:text-zinc-400">
                            {{ __('Este evento está asociado a la siguiente convocatoria:') }}
                        </p>
                        <x-ui.button 
                            href="{{ route('convocatorias.show', $event->call->slug) }}" 
                            variant="primary"
                            icon="arrow-right"
                            navigate
                        >
                            {{ __('Ver convocatoria') }}: {{ $event->call->title }}
                        </x-ui.button>
                    </div>
                @endif
            </div>
            
            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Event Details Card --}}
                <x-ui.card>
                    <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ __('Información del evento') }}
                    </h3>
                    
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                {{ __('Fecha de inicio') }}
                            </dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                {{ $event->start_date->translatedFormat('l, d F Y') }}
                                @if($event->start_date->format('H:i') !== '00:00')
                                    <br>
                                    <span class="text-zinc-600 dark:text-zinc-400">
                                        {{ __('Hora:') }} {{ $event->start_date->format('H:i') }}
                                    </span>
                                @endif
                            </dd>
                        </div>
                        
                        @if($event->end_date)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ __('Fecha de fin') }}
                                </dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $event->end_date->translatedFormat('l, d F Y') }}
                                    @if($event->end_date->format('H:i') !== '00:00')
                                        <br>
                                        <span class="text-zinc-600 dark:text-zinc-400">
                                            {{ __('Hora:') }} {{ $event->end_date->format('H:i') }}
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            
                            @if($event->duration())
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                        {{ __('Duración') }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                        {{ trans_choice(':count hora|:count horas', (int)$event->duration(), ['count' => (int)$event->duration()]) }}
                                    </dd>
                                </div>
                            @endif
                        @endif
                        
                        @if($event->location)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ __('Ubicación') }}
                                </dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $event->location }}
                                </dd>
                            </div>
                        @endif
                        
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                {{ __('Tipo de evento') }}
                            </dt>
                            <dd class="mt-1">
                                <x-ui.badge :color="$eventTypeConfig['color']" :icon="$eventTypeConfig['icon']">
                                    {{ $eventTypeConfig['label'] }}
                                </x-ui.badge>
                            </dd>
                        </div>
                        
                        @if($event->program)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ __('Programa') }}
                                </dt>
                                <dd class="mt-1">
                                    <x-ui.button 
                                        href="{{ route('programas.show', $event->program->slug) }}" 
                                        variant="ghost"
                                        size="sm"
                                        navigate
                                    >
                                        {{ $event->program->name }}
                                    </x-ui.button>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>
                
                {{-- Navigation Actions --}}
                <x-ui.card>
                    <div class="space-y-3">
                        <x-ui.button 
                            href="{{ route('eventos.index') }}" 
                            variant="outline"
                            icon="arrow-left"
                            class="w-full"
                            navigate
                        >
                            {{ __('Volver al listado') }}
                        </x-ui.button>
                        <x-ui.button 
                            href="{{ route('calendario') }}" 
                            variant="outline"
                            icon="calendar"
                            class="w-full"
                            navigate
                        >
                            {{ __('Ver calendario') }}
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </x-ui.section>

    {{-- Related Events --}}
    @if($this->relatedEvents->isNotEmpty())
        <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Eventos relacionados') }}
                </h2>
                <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ __('Otros eventos que pueden interesarte') }}
                </p>
            </div>
            
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($this->relatedEvents as $relatedEvent)
                    <x-content.event-card 
                        :event="$relatedEvent"
                        :href="route('eventos.show', $relatedEvent->id)"
                        variant="compact"
                        :showCall="false"
                    />
                @endforeach
            </div>
        </x-ui.section>
    @endif
</div>

