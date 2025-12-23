@props([
    'transparent' => false, // For hero sections with background images
])

@php
    $navItems = [
        ['label' => __('Inicio'), 'route' => 'home', 'icon' => 'home'],
        ['label' => __('Programas'), 'route' => 'programas.index', 'icon' => 'academic-cap'],
        ['label' => __('Convocatorias'), 'route' => 'convocatorias.index', 'icon' => 'document-text'],
        ['label' => __('Noticias'), 'route' => 'noticias.index', 'icon' => 'newspaper'],
        ['label' => __('Documentos'), 'route' => 'documentos.index', 'icon' => 'folder-open'],
        ['label' => __('Calendario'), 'route' => 'home', 'icon' => 'calendar-days'], // TODO: Change to events.index
    ];
    
    $bgClasses = $transparent 
        ? 'bg-transparent' 
        : 'bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800';
@endphp

<header {{ $attributes->merge(['class' => "sticky top-0 z-50 $bgClasses"]) }}>
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between lg:h-20">
            {{-- Logo --}}
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-erasmus-600 to-erasmus-700 text-white shadow-md lg:h-12 lg:w-12">
                        <span class="text-lg font-bold lg:text-xl">E+</span>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-base font-semibold text-zinc-900 dark:text-white lg:text-lg">Erasmus+</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Centro Murcia</p>
                    </div>
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <div class="hidden lg:flex lg:items-center lg:gap-1">
                @foreach($navItems as $item)
                    <a 
                        href="{{ route($item['route']) }}" 
                        wire:navigate
                        @class([
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            'text-erasmus-700 bg-erasmus-50 dark:text-erasmus-300 dark:bg-erasmus-900/30' => request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'),
                            'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-800' => !request()->routeIs($item['route']) && !request()->routeIs($item['route'].'.*'),
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- Right Side: Auth Links & Mobile Menu Toggle --}}
            <div class="flex items-center gap-3">
                {{-- Auth Links (Desktop) --}}
                <div class="hidden items-center gap-2 sm:flex">
                    @auth
                        <a 
                            href="{{ route('dashboard') }}" 
                            wire:navigate
                            class="inline-flex items-center gap-2 rounded-lg bg-erasmus-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-erasmus-700 focus:outline-none focus:ring-2 focus:ring-erasmus-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                        >
                            <flux:icon name="squares-2x2" class="[:where(&)]:size-4" variant="outline" />
                            {{ __('Panel') }}
                        </a>
                    @else
                        <a 
                            href="{{ route('login') }}" 
                            wire:navigate
                            class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-600 transition-colors hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white"
                        >
                            {{ __('Iniciar sesión') }}
                        </a>
                        @if (Route::has('register'))
                            <a 
                                href="{{ route('register') }}" 
                                wire:navigate
                                class="inline-flex items-center rounded-lg bg-erasmus-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-erasmus-700 focus:outline-none focus:ring-2 focus:ring-erasmus-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                            >
                                {{ __('Registrarse') }}
                            </a>
                        @endif
                    @endauth
                </div>

                {{-- Mobile Menu Button --}}
                <div class="lg:hidden" x-data="{ open: false }">
                    <button 
                        @click="open = !open"
                        type="button" 
                        class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-erasmus-500 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                        :aria-expanded="open"
                    >
                        <span class="sr-only">{{ __('Abrir menú') }}</span>
                        <flux:icon x-show="!open" name="bars-3" class="[:where(&)]:size-6" variant="outline" />
                        <flux:icon x-show="open" x-cloak name="x-mark" class="[:where(&)]:size-6" variant="outline" />
                    </button>

                    {{-- Mobile Menu Panel --}}
                    <div 
                        x-show="open" 
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        @click.away="open = false"
                        class="absolute inset-x-0 top-full border-b border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                    >
                        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                            <div class="grid gap-1">
                                @foreach($navItems as $item)
                                    <a 
                                        href="{{ route($item['route']) }}" 
                                        wire:navigate
                                        @click="open = false"
                                        @class([
                                            'flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors',
                                            'text-erasmus-700 bg-erasmus-50 dark:text-erasmus-300 dark:bg-erasmus-900/30' => request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'),
                                            'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-800' => !request()->routeIs($item['route']) && !request()->routeIs($item['route'].'.*'),
                                        ])
                                    >
                                        <flux:icon :name="$item['icon']" class="[:where(&)]:size-5 text-zinc-400" variant="outline" />
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>

                            {{-- Mobile Auth Links --}}
                            <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700 sm:hidden">
                                @auth
                                    <a 
                                        href="{{ route('dashboard') }}" 
                                        wire:navigate
                                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-erasmus-600 px-4 py-3 text-base font-medium text-white"
                                    >
                                        <flux:icon name="squares-2x2" class="[:where(&)]:size-5" variant="outline" />
                                        {{ __('Panel de control') }}
                                    </a>
                                @else
                                    <div class="grid gap-2">
                                        <a 
                                            href="{{ route('login') }}" 
                                            wire:navigate
                                            class="flex w-full items-center justify-center rounded-lg border border-zinc-300 px-4 py-3 text-base font-medium text-zinc-700 dark:border-zinc-600 dark:text-zinc-300"
                                        >
                                            {{ __('Iniciar sesión') }}
                                        </a>
                                        @if (Route::has('register'))
                                            <a 
                                                href="{{ route('register') }}" 
                                                wire:navigate
                                                class="flex w-full items-center justify-center rounded-lg bg-erasmus-600 px-4 py-3 text-base font-medium text-white"
                                            >
                                                {{ __('Registrarse') }}
                                            </a>
                                        @endif
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
