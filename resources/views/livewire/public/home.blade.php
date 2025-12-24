<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="hero-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#hero-pattern)" width="100%" height="100%" />
            </svg>
        </div>
        
        {{-- EU Stars decoration --}}
        <div class="absolute right-0 top-0 -translate-y-1/4 translate-x-1/4 opacity-5">
            <svg class="h-96 w-96" viewBox="0 0 100 100">
                @for($i = 0; $i < 12; $i++)
                    <polygon 
                        points="50,15 53,35 75,35 57,47 63,67 50,55 37,67 43,47 25,35 47,35" 
                        fill="currentColor" 
                        transform="rotate({{ $i * 30 }} 50 50) translate(0 -30)"
                    />
                @endfor
            </svg>
        </div>
        
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36">
            <div class="max-w-3xl">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <flux:icon name="globe-europe-africa" class="[:where(&)]:size-5" variant="outline" />
                    {{ __('common.home.program_title') }}
                </div>
                
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ __('common.home.hero_title') }}
                </h1>
                
                <p class="mt-6 text-lg leading-relaxed text-erasmus-100 sm:text-xl">
                    {{ __('common.home.hero_description') }}
                </p>
                
                <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
                    <x-ui.button 
                        href="{{ route('convocatorias.index') }}" 
                        size="lg"
                        variant="secondary"
                        icon="arrow-right"
                        navigate
                    >
                        {{ __('common.actions.view_calls') }}
                    </x-ui.button>
                    
                    <x-ui.button 
                        href="{{ route('programas.index') }}" 
                        size="lg"
                        variant="ghost"
                        class="text-white hover:bg-white/10"
                        navigate
                    >
                        {{ __('common.actions.learn_programs') }}
                    </x-ui.button>
                </div>
            </div>
            
            {{-- Stats --}}
            <div class="mt-16 grid grid-cols-2 gap-6 border-t border-white/10 pt-10 sm:grid-cols-4">
                <div class="text-center sm:text-left">
                    <div class="text-3xl font-bold text-white sm:text-4xl">{{ $programs->count() }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('common.home.active_programs') }}</div>
                </div>
                <div class="text-center sm:text-left">
                    <div class="text-3xl font-bold text-white sm:text-4xl">{{ $calls->count() }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('common.home.open_calls') }}</div>
                </div>
                <div class="text-center sm:text-left">
                    <div class="text-3xl font-bold text-white sm:text-4xl">27+</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('common.home.destination_countries') }}</div>
                </div>
                <div class="text-center sm:text-left">
                    <div class="text-3xl font-bold text-white sm:text-4xl">{{ $events->count() }}</div>
                    <div class="mt-1 text-sm text-erasmus-200">{{ __('common.home.upcoming_events') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Programs Section --}}
    <x-ui.section id="programas" class="bg-zinc-50 dark:bg-zinc-900">
        <x-slot:title>{{ __('common.home.programs_title') }}</x-slot:title>
        <x-slot:description>{{ __('common.home.programs_description') }}</x-slot:description>
        <x-slot:actions>
            <x-ui.button href="{{ route('programas.index') }}" variant="outline" icon="arrow-right" navigate>
                {{ __('common.actions.view_all_programs') }}
            </x-ui.button>
        </x-slot:actions>
        
        @if($programs->isEmpty())
            <x-ui.empty-state 
                :title="__('common.home.no_programs_title')"
                :description="__('common.home.no_programs_description')"
                icon="academic-cap"
            />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($programs as $program)
                    <x-content.program-card 
                        :program="$program" 
                        variant="featured"
                    />
                @endforeach
            </div>
        @endif
    </x-ui.section>

    {{-- Calls Section --}}
    <x-ui.section id="convocatorias">
        <x-slot:title>{{ __('common.home.calls_title') }}</x-slot:title>
        <x-slot:description>{{ __('common.home.calls_description') }}</x-slot:description>
        <x-slot:actions>
            <x-ui.button href="{{ route('convocatorias.index') }}" variant="outline" icon="arrow-right" navigate>
                {{ __('common.actions.view_all_calls') }}
            </x-ui.button>
        </x-slot:actions>
        
        @if($calls->isEmpty())
            <x-ui.empty-state 
                :title="__('common.home.no_calls_title')"
                :description="__('common.home.no_calls_description')"
                icon="megaphone"
            />
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach($calls as $call)
                    <x-content.call-card 
                        :call="$call" 
                        variant="featured"
                    />
                @endforeach
            </div>
        @endif
    </x-ui.section>

    {{-- News and Events Grid --}}
    <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
        <div class="grid gap-12 lg:grid-cols-5 lg:gap-8">
            {{-- News Column --}}
            <div class="lg:col-span-3">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('common.home.latest_news') }}
                        </h2>
                        <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ __('common.home.news_description') }}
                        </p>
                    </div>
                    <x-ui.button href="#" variant="ghost" icon="arrow-right" size="sm">
                        {{ __('common.actions.see_all') }}
                    </x-ui.button>
                </div>
                
                @if($news->isEmpty())
                    <x-ui.empty-state 
                        :title="__('common.home.no_news_title')"
                        :description="__('common.home.no_news_description')"
                        icon="newspaper"
                        size="sm"
                    />
                @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        @foreach($news as $index => $newsItem)
                            <x-content.news-card 
                                :news="$newsItem"
                                :variant="$index === 0 ? 'featured' : 'default'"
                                :class="$index === 0 ? 'sm:col-span-2 xl:col-span-2' : ''"
                            />
                        @endforeach
                    </div>
                @endif
            </div>
            
            {{-- Events Column --}}
            <div class="lg:col-span-2">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('common.home.upcoming_events_title') }}
                        </h2>
                        <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ __('common.home.events_description') }}
                        </p>
                    </div>
                    <x-ui.button href="{{ route('calendario') }}" variant="ghost" icon="arrow-right" size="sm" navigate>
                        {{ __('common.nav.calendar') }}
                    </x-ui.button>
                </div>
                
                @if($events->isEmpty())
                    <x-ui.empty-state 
                        :title="__('common.home.no_events_title')"
                        :description="__('common.home.no_events_description')"
                        icon="calendar"
                        size="sm"
                    />
                @else
                    <div class="space-y-0">
                        @foreach($events as $event)
                            <x-content.event-card 
                                :event="$event"
                                variant="timeline"
                            />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-ui.section>

    {{-- Newsletter Section --}}
    <x-ui.section class="bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        <div class="mx-auto max-w-4xl text-center">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                <flux:icon name="envelope" class="[:where(&)]:size-5" variant="outline" />
                {{ __('common.home.newsletter_title') }}
            </div>
            
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                {{ __('common.home.newsletter_heading') }}
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-erasmus-100">
                {{ __('common.home.newsletter_description') }}
            </p>
            
            <div class="mt-8">
                <x-ui.button 
                    href="{{ route('newsletter.subscribe') }}" 
                    size="lg" 
                    variant="secondary"
                    icon="envelope"
                    navigate
                >
                    {{ __('common.actions.subscribe_newsletter') }}
                </x-ui.button>
            </div>
            
            <p class="mt-4 text-sm text-erasmus-200">
                {{ __('common.home.newsletter_cancel') }}
            </p>
        </div>
    </x-ui.section>

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r from-erasmus-600 to-erasmus-700">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    {{ __('common.home.cta_title') }}
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-erasmus-100">
                    {{ __('common.home.cta_description') }}
                </p>
                <div class="mt-8 flex flex-col justify-center gap-4 sm:flex-row">
                    <x-ui.button 
                        href="{{ route('convocatorias.index') }}" 
                        size="lg" 
                        variant="secondary"
                        navigate
                    >
                        {{ __('common.actions.explore_calls') }}
                    </x-ui.button>
                    <x-ui.button 
                        href="#" 
                        size="lg" 
                        variant="ghost"
                        class="text-white hover:bg-white/10"
                    >
                        {{ __('common.actions.contact_us') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>
</div>
