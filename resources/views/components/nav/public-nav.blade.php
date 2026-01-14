@props([
    'transparent' => false, // For hero sections with background images
])

{{--
    Componente de Navegación Pública
    
    Características:
    - Menú responsive (desktop y móvil)
    - Selector de idioma integrado
    - Enlaces según autenticación y permisos
    - Logo y nombre del centro configurable
    - Soporte para modo transparente (hero sections)
    
    Props:
    - transparent: Si es true, el fondo es transparente (útil para hero sections)
--}}

@php
    // Elementos del menú de navegación principal
    $navItems = [
        ['label' => __('common.nav.home'), 'route' => 'home', 'icon' => 'home'],
        ['label' => __('common.nav.programs'), 'route' => 'programas.index', 'icon' => 'academic-cap'],
        ['label' => __('common.nav.calls'), 'route' => 'convocatorias.index', 'icon' => 'document-text'],
        ['label' => __('common.nav.news'), 'route' => 'noticias.index', 'icon' => 'newspaper'],
        ['label' => __('common.nav.documents'), 'route' => 'documentos.index', 'icon' => 'folder-open'],
        ['label' => __('common.nav.calendar'), 'route' => 'calendario', 'icon' => 'calendar-days'],
        ['label' => __('common.search.global_title'), 'route' => 'search', 'icon' => 'magnifying-glass'],
    ];
    
    // Clases de fondo según el modo (transparente o normal)
    $bgClasses = $transparent 
        ? 'bg-transparent' 
        : 'bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800';
@endphp

<header {{ $attributes->merge(['class' => "sticky top-0 z-50 $bgClasses"]) }}>
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between lg:h-20">
            {{-- Logo y Nombre del Centro --}}
            <div class="flex items-center">
                <a 
                    href="{{ route('home') }}" 
                    class="flex items-center gap-3" 
                    wire:navigate
                    aria-label="{{ __('common.nav.home') }}"
                >
                    @php
                        // Obtener logo y nombre del centro desde configuración
                        $centerLogo = \App\Models\Setting::get('center_logo');
                        $centerName = \App\Models\Setting::get('center_name', 'Erasmus+ Centro (Murcia)');
                    @endphp
                    @if($centerLogo)
                        <img 
                            src="{{ $centerLogo }}" 
                            alt="{{ $centerName }}"
                            class="h-10 w-auto max-w-[150px] object-contain lg:h-12 lg:max-w-[180px]"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        />
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-erasmus-600 to-erasmus-700 text-white shadow-md lg:h-12 lg:w-12" style="display: none;">
                            <span class="text-lg font-bold lg:text-xl">E+</span>
                        </div>
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-erasmus-600 to-erasmus-700 text-white shadow-md lg:h-12 lg:w-12">
                            <span class="text-lg font-bold lg:text-xl">E+</span>
                        </div>
                    @endif
                    <div class="hidden sm:block">
                        <p class="text-base font-semibold text-zinc-900 dark:text-white lg:text-lg">{{ $centerName }}</p>
                    </div>
                </a>
            </div>

            {{-- Navegación Desktop (oculta en móvil) --}}
            <div class="hidden lg:flex lg:items-center lg:gap-1">
                @foreach($navItems as $item)
                    <a 
                        href="{{ route($item['route']) }}" 
                        wire:navigate
                        @class([
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            // Estado activo: ruta actual o sub-rutas
                            'text-erasmus-700 bg-erasmus-50 dark:text-erasmus-300 dark:bg-erasmus-900/30' => request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'),
                            // Estado normal: hover y transiciones
                            'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-800' => !request()->routeIs($item['route']) && !request()->routeIs($item['route'].'.*'),
                        ])
                        aria-current="{{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'page' : 'false' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- Right Side: Language Switcher, Auth Links & Mobile Menu Toggle --}}
            <div class="flex items-center gap-3">
                {{-- Language Switcher (Desktop) --}}
                <div class="hidden sm:block">
                    <livewire:language.switcher variant="dropdown" size="md" />
                </div>

                {{-- Auth Links (Desktop) --}}
                <div class="hidden items-center gap-2 sm:flex">
                    @auth
                        {{-- Panel de Administración (solo si tiene permisos) --}}
                        @if(auth()->user()?->can('viewAny', \App\Models\Program::class) || auth()->user()?->can('viewAny', \App\Models\User::class))
                            <a 
                                href="{{ route('admin.dashboard') }}" 
                                wire:navigate
                                class="inline-flex items-center gap-2 rounded-lg bg-erasmus-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-erasmus-700 focus:outline-none focus:ring-2 focus:ring-erasmus-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                                title="{{ __('common.nav.admin_panel') }}"
                            >
                                <flux:icon name="squares-2x2" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.nav.admin_panel') }}
                            </a>
                        @else
                            {{-- Dashboard normal (si está autenticado pero no tiene permisos de admin) --}}
                            <a 
                                href="{{ route('dashboard') }}" 
                                wire:navigate
                                class="inline-flex items-center gap-2 rounded-lg bg-erasmus-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-erasmus-700 focus:outline-none focus:ring-2 focus:ring-erasmus-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                            >
                                <flux:icon name="squares-2x2" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.nav.dashboard') }}
                            </a>
                        @endif
                    @else
                        {{-- Usuario no autenticado: mostrar login y registro --}}
                        <a 
                            href="{{ route('login') }}" 
                            wire:navigate
                            class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-600 transition-colors hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white"
                        >
                            {{ __('common.nav.login') }}
                        </a>
                        @if (Route::has('register'))
                            <a 
                                href="{{ route('register') }}" 
                                wire:navigate
                                class="inline-flex items-center rounded-lg bg-erasmus-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-erasmus-700 focus:outline-none focus:ring-2 focus:ring-erasmus-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                            >
                                {{ __('common.nav.register') }}
                            </a>
                        @endif
                    @endauth
                </div>

                {{-- Botón de Menú Móvil (solo visible en móvil) --}}
                <div class="lg:hidden" x-data="{ open: false }">
                    <button 
                        @click="open = !open"
                        type="button" 
                        class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-erasmus-500 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                        :aria-expanded="open"
                        :aria-label="open ? '{{ __('common.nav.close_menu') }}' : '{{ __('common.nav.open_menu') }}'"
                    >
                        <span class="sr-only">{{ __('common.nav.open_menu') }}</span>
                        {{-- Icono de menú (hamburguesa) --}}
                        <flux:icon x-show="!open" name="bars-3" class="[:where(&)]:size-6" variant="outline" />
                        {{-- Icono de cerrar (X) --}}
                        <flux:icon x-show="open" x-cloak name="x-mark" class="[:where(&)]:size-6" variant="outline" />
                    </button>

                    {{-- Panel de Menú Móvil (desplegable) --}}
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
                        role="menu"
                    >
                        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                            {{-- Enlaces de Navegación Móvil --}}
                            <div class="grid gap-1" role="menubar">
                                @foreach($navItems as $item)
                                    <a 
                                        href="{{ route($item['route']) }}" 
                                        wire:navigate
                                        @click="open = false"
                                        @class([
                                            'flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors',
                                            // Estado activo
                                            'text-erasmus-700 bg-erasmus-50 dark:text-erasmus-300 dark:bg-erasmus-900/30' => request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'),
                                            // Estado normal
                                            'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-800' => !request()->routeIs($item['route']) && !request()->routeIs($item['route'].'.*'),
                                        ])
                                        role="menuitem"
                                        aria-current="{{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'page' : 'false' }}"
                                    >
                                        <flux:icon :name="$item['icon']" class="[:where(&)]:size-5 text-zinc-400" variant="outline" />
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>

                            {{-- Mobile Language Switcher --}}
                            <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700 sm:hidden">
                                <div class="mb-4">
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ __('common.language.select') }}
                                    </label>
                                    <livewire:language.switcher variant="select" size="md" />
                                </div>
                            </div>

                            {{-- Mobile Auth Links --}}
                            <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700 sm:hidden">
                                @auth
                                    {{-- Panel de Administración (solo si tiene permisos) --}}
                                    @if(auth()->user()?->can('viewAny', \App\Models\Program::class) || auth()->user()?->can('viewAny', \App\Models\User::class))
                                        <a 
                                            href="{{ route('admin.dashboard') }}" 
                                            wire:navigate
                                            @click="open = false"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-erasmus-600 px-4 py-3 text-base font-medium text-white"
                                        >
                                            <flux:icon name="squares-2x2" class="[:where(&)]:size-5" variant="outline" />
                                            {{ __('common.nav.admin_panel') }}
                                        </a>
                                    @else
                                        {{-- Dashboard normal (si está autenticado pero no tiene permisos de admin) --}}
                                        <a 
                                            href="{{ route('dashboard') }}" 
                                            wire:navigate
                                            @click="open = false"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-erasmus-600 px-4 py-3 text-base font-medium text-white"
                                        >
                                            <flux:icon name="squares-2x2" class="[:where(&)]:size-5" variant="outline" />
                                            {{ __('common.nav.dashboard_panel') }}
                                        </a>
                                    @endif
                                @else
                                    {{-- Usuario no autenticado: mostrar login y registro --}}
                                    <div class="grid gap-2">
                                        <a 
                                            href="{{ route('login') }}" 
                                            wire:navigate
                                            @click="open = false"
                                            class="flex w-full items-center justify-center rounded-lg border border-zinc-300 px-4 py-3 text-base font-medium text-zinc-700 dark:border-zinc-600 dark:text-zinc-300"
                                        >
                                            {{ __('common.nav.login') }}
                                        </a>
                                        @if (Route::has('register'))
                                            <a 
                                                href="{{ route('register') }}" 
                                                wire:navigate
                                                @click="open = false"
                                                class="flex w-full items-center justify-center rounded-lg bg-erasmus-600 px-4 py-3 text-base font-medium text-white"
                                            >
                                                {{ __('common.nav.register') }}
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
