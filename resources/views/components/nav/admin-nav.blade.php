{{--
    Componente de Navegación de Administración
    
    Características:
    - Navegación basada en permisos usando @can
    - Grupos organizados lógicamente (Platform, Contenido, Gestión, Sistema)
    - Enlaces con indicadores de ruta actual
    - Integrado con Flux UI Navlist
    
    Nota: Este componente se incluye en el sidebar de administración
--}}

<flux:navlist variant="outline">
    {{-- Platform: Dashboard --}}
    <flux:navlist.group :heading="__('Platform')" class="grid">
        <flux:navlist.item 
            icon="squares-2x2" 
            :href="route('admin.dashboard')" 
            :current="request()->routeIs('admin.dashboard')" 
            wire:navigate
        >
            {{ __('Dashboard') }}
        </flux:navlist.item>
    </flux:navlist.group>

    {{-- Contenido: Programas, Convocatorias, Noticias, Documentos, Eventos --}}
    @php
        $hasContentAccess = auth()->user()?->can('viewAny', \App\Models\Program::class) 
            || auth()->user()?->can('viewAny', \App\Models\Call::class)
            || auth()->user()?->can('viewAny', \App\Models\NewsPost::class)
            || auth()->user()?->can('viewAny', \App\Models\Document::class)
            || auth()->user()?->can('viewAny', \App\Models\ErasmusEvent::class);
    @endphp

    @if($hasContentAccess)
        <flux:navlist.group :heading="__('common.admin.nav.content')" class="grid">
            {{-- Elementos principales de contenido --}}
            
            {{-- Programas --}}
            @can('viewAny', \App\Models\Program::class)
                <flux:navlist.item 
                    icon="academic-cap" 
                    :href="route('admin.programs.index')" 
                    :current="request()->routeIs('admin.programs.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.programs') }}
                </flux:navlist.item>
            @endcan

            {{-- Convocatorias --}}
            @can('viewAny', \App\Models\Call::class)
                <flux:navlist.item 
                    icon="document-text" 
                    :href="route('admin.calls.index')" 
                    :current="request()->routeIs('admin.calls.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.calls') }}
                </flux:navlist.item>
            @endcan

            {{-- Noticias y elementos relacionados --}}
            @can('viewAny', \App\Models\NewsPost::class)
                <flux:navlist.item 
                    icon="newspaper" 
                    :href="route('admin.news.index')" 
                    :current="request()->routeIs('admin.news.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.news') }}
                </flux:navlist.item>
            @endcan

            {{-- Etiquetas de Noticias (relacionado con Noticias) --}}
            @can('viewAny', \App\Models\NewsTag::class)
                <flux:navlist.item 
                    icon="tag" 
                    :href="route('admin.news-tags.index')" 
                    :current="request()->routeIs('admin.news-tags.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.news_tags') }}
                </flux:navlist.item>
            @endcan

            {{-- Documentos y elementos relacionados --}}
            @can('viewAny', \App\Models\Document::class)
                <flux:navlist.item 
                    icon="document" 
                    :href="route('admin.documents.index')" 
                    :current="request()->routeIs('admin.documents.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.documents') }}
                </flux:navlist.item>
            @endcan

            {{-- Categorías de Documentos (relacionado con Documentos) --}}
            @can('viewAny', \App\Models\DocumentCategory::class)
                <flux:navlist.item 
                    icon="folder" 
                    :href="route('admin.document-categories.index')" 
                    :current="request()->routeIs('admin.document-categories.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.document_categories') }}
                </flux:navlist.item>
            @endcan

            {{-- Eventos --}}
            @can('viewAny', \App\Models\ErasmusEvent::class)
                <flux:navlist.item 
                    icon="calendar-days" 
                    :href="route('admin.events.index')" 
                    :current="request()->routeIs('admin.events.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.events') }}
                </flux:navlist.item>
            @endcan
        </flux:navlist.group>
    @endif

    {{-- Gestión: Años Académicos --}}
    @can('viewAny', \App\Models\AcademicYear::class)
        <flux:navlist.group :heading="__('common.admin.nav.management')" class="grid">
            <flux:navlist.item 
                icon="calendar" 
                :href="route('admin.academic-years.index')" 
                :current="request()->routeIs('admin.academic-years.*')" 
                wire:navigate
            >
                {{ __('common.nav.academic_years') }}
            </flux:navlist.item>
        </flux:navlist.group>
    @endcan

    {{-- Sistema: Usuarios, Roles, Configuración, Traducciones, Auditoría, Newsletter --}}
    @can('viewAny', \App\Models\User::class)
        <flux:navlist.group :heading="__('common.admin.nav.system')" class="grid">
            {{-- Usuarios --}}
            <flux:navlist.item 
                icon="user-group" 
                :href="route('admin.users.index')" 
                :current="request()->routeIs('admin.users.*')" 
                wire:navigate
            >
                {{ __('common.nav.users') }}
            </flux:navlist.item>

            {{-- Roles y Permisos --}}
            @can('viewAny', \Spatie\Permission\Models\Role::class)
                <flux:navlist.item 
                    icon="shield-check" 
                    :href="route('admin.roles.index')" 
                    :current="request()->routeIs('admin.roles.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.roles') }}
                </flux:navlist.item>
            @endcan

            {{-- Configuración --}}
            @can('viewAny', \App\Models\Setting::class)
                <flux:navlist.item 
                    icon="cog-6-tooth" 
                    :href="route('admin.settings.index')" 
                    :current="request()->routeIs('admin.settings.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.settings') }}
                </flux:navlist.item>
            @endcan

            {{-- Traducciones --}}
            @can('viewAny', \App\Models\Translation::class)
                <flux:navlist.item 
                    icon="language" 
                    :href="route('admin.translations.index')" 
                    :current="request()->routeIs('admin.translations.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.translations') }}
                </flux:navlist.item>
            @endcan

            {{-- Auditoría y Logs --}}
            @can('viewAny', \Spatie\Activitylog\Models\Activity::class)
                <flux:navlist.item 
                    icon="clipboard-document-list" 
                    :href="route('admin.audit-logs.index')" 
                    :current="request()->routeIs('admin.audit-logs.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.audit_logs') }}
                </flux:navlist.item>
            @endcan

            {{-- Suscripciones Newsletter --}}
            @can('viewAny', \App\Models\NewsletterSubscription::class)
                <flux:navlist.item 
                    icon="envelope" 
                    :href="route('admin.newsletter.index')" 
                    :current="request()->routeIs('admin.newsletter.*')" 
                    wire:navigate
                >
                    {{ __('common.nav.newsletter_subscriptions') }}
                </flux:navlist.item>
            @endcan
        </flux:navlist.group>
    @endcan
</flux:navlist>
