<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $event->title }}
                    </h1>
                    @php
                        $statusConfig = $this->getEventStatusConfig();
                    @endphp
                    <x-ui.badge :variant="$statusConfig['variant']" size="lg">
                        {{ $statusConfig['label'] }}
                    </x-ui.badge>
                    @if($event->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @endif
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    @php
                        $typeConfig = $this->getEventTypeConfig($event->event_type);
                    @endphp
                    <x-ui.badge :variant="$typeConfig['variant']" size="sm" :icon="$typeConfig['icon']">
                        {{ $typeConfig['label'] }}
                    </x-ui.badge>
                    @if($event->location)
                        · <flux:icon name="map-pin" class="[:where(&)]:size-4 inline" variant="outline" />
                        {{ $event->location }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$event->trashed())
                    @can('update', $event)
                        <flux:button 
                            href="{{ route('admin.events.edit', $event) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endcan
                @endif
                <flux:button 
                    href="{{ route('admin.events.index') }}" 
                    variant="ghost"
                    wire:navigate
                    icon="arrow-left"
                >
                    {{ __('common.actions.back') }}
                </flux:button>
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Eventos Erasmus+'), 'href' => route('admin.events.index'), 'icon' => 'calendar'],
                ['label' => $event->title, 'icon' => 'eye'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Featured Image --}}
            @if($this->featuredImageUrl)
                <x-ui.card>
                    <img 
                        src="{{ $this->featuredImageUrl }}" 
                        alt="{{ $event->title }}"
                        class="w-full rounded-lg object-cover"
                        loading="lazy"
                    />
                </x-ui.card>
            @endif

            {{-- Description --}}
            @if($event->description)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Descripción') }}</flux:heading>
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                {{ $event->description }}
                            </p>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Dates and Location --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Fechas y Ubicación') }}</flux:heading>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="rounded-lg bg-erasmus-100 p-2 dark:bg-erasmus-900/30">
                                <flux:icon name="calendar" class="[:where(&)]:size-5 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Fecha de Inicio') }}</p>
                                <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                                    {{ $event->start_date->translatedFormat('l, d F Y') }}
                                </p>
                                @if(!$event->isAllDay())
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $event->start_date->format('H:i') }}
                                    </p>
                                @else
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Todo el día') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if($event->end_date)
                            <div class="flex items-start gap-3">
                                <div class="rounded-lg bg-erasmus-100 p-2 dark:bg-erasmus-900/30">
                                    <flux:icon name="calendar-days" class="[:where(&)]:size-5 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Fecha de Fin') }}</p>
                                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                                        {{ $event->end_date->translatedFormat('l, d F Y') }}
                                    </p>
                                    @if(!$event->isAllDay())
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $event->end_date->format('H:i') }}
                                        </p>
                                    @else
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ __('Todo el día') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($event->location)
                            <div class="flex items-start gap-3">
                                <div class="rounded-lg bg-erasmus-100 p-2 dark:bg-erasmus-900/30">
                                    <flux:icon name="map-pin" class="[:where(&)]:size-5 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Ubicación') }}</p>
                                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                                        {{ $event->location }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Associations --}}
            @if($event->program || $event->call)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Asociaciones') }}</flux:heading>
                        <div class="space-y-4">
                            @if($event->program)
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                        <flux:icon name="academic-cap" class="[:where(&)]:size-5 text-blue-600 dark:text-blue-400" variant="outline" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Programa') }}</p>
                                        <a 
                                            href="{{ route('admin.programs.show', $event->program) }}" 
                                            wire:navigate
                                            class="text-lg font-semibold text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300"
                                        >
                                            {{ $event->program->name }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($event->call)
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                                        <flux:icon name="megaphone" class="[:where(&)]:size-5 text-green-600 dark:text-green-400" variant="outline" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Convocatoria') }}</p>
                                        <a 
                                            href="{{ route('admin.calls.show', $event->call) }}" 
                                            wire:navigate
                                            class="text-lg font-semibold text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300"
                                        >
                                            {{ $event->call->title }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Images Gallery --}}
            @if($this->hasImages())
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Galería de Imágenes') }}</flux:heading>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                            @foreach($this->images as $media)
                                <div class="relative group">
                                    <img 
                                        src="{{ $media->getUrl('medium') ?? $media->getUrl() }}" 
                                        alt="{{ $media->name ?? __('Imagen') }}"
                                        class="h-48 w-full rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                        loading="lazy"
                                    />
                                    <a 
                                        href="{{ $media->getUrl() }}" 
                                        target="_blank"
                                        class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition-opacity group-hover:opacity-100 rounded-lg"
                                    >
                                        <flux:icon name="magnifying-glass-plus" class="[:where(&)]:size-6 text-white" variant="outline" />
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Statistics --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Estadísticas') }}</flux:heading>
                    <div class="space-y-4">
                        @if($this->statistics['duration'])
                            <div>
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Duración') }}</p>
                                <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                                    {{ number_format($this->statistics['duration'], 1) }} {{ __('horas') }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Tipo de evento') }}</p>
                            <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $typeConfig['label'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Imágenes') }}</p>
                            <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $this->statistics['images_count'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Visibility --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Visibilidad') }}</flux:heading>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Evento público') }}</p>
                            <x-ui.badge :variant="$event->is_public ? 'success' : 'neutral'" size="sm" class="mt-1">
                                {{ $event->is_public ? __('Sí') : __('No') }}
                            </x-ui.badge>
                        </div>
                        @if(!$event->trashed())
                            @can('update', $event)
                                <flux:button 
                                    wire:click="togglePublic"
                                    variant="ghost"
                                    size="sm"
                                    icon="{{ $event->is_public ? 'eye-slash' : 'eye' }}"
                                    wire:loading.attr="disabled"
                                    wire:target="togglePublic"
                                >
                                    {{ $event->is_public ? __('Ocultar') : __('Mostrar') }}
                                </flux:button>
                            @endcan
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Metadata --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Metadatos') }}</flux:heading>
                    <div class="space-y-3">
                        @if($event->creator)
                            <div>
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Creado por') }}</p>
                                <p class="text-sm text-zinc-900 dark:text-white">
                                    {{ $event->creator->name }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Fecha de creación') }}</p>
                            <p class="text-sm text-zinc-900 dark:text-white">
                                {{ $event->created_at->translatedFormat('d F Y H:i') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Última actualización') }}</p>
                            <p class="text-sm text-zinc-900 dark:text-white">
                                {{ $event->updated_at->translatedFormat('d F Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Actions --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Acciones') }}</flux:heading>
                    <div class="flex flex-col gap-2">
                        @if(!$event->trashed())
                            @can('update', $event)
                                <flux:button 
                                    href="{{ route('admin.events.edit', $event) }}" 
                                    variant="primary"
                                    wire:navigate
                                    icon="pencil"
                                    class="w-full"
                                >
                                    {{ __('common.actions.edit') }}
                                </flux:button>
                            @endcan

                            @can('delete', $event)
                                <flux:button 
                                    wire:click="confirmDelete"
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                >
                                    {{ __('common.actions.delete') }}
                                </flux:button>
                            @endcan
                        @else
                            @can('restore', $event)
                                <flux:button 
                                    wire:click="confirmRestore"
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('Restaurar') }}
                                </flux:button>
                            @endcan

                            @can('forceDelete', $event)
                                <flux:button 
                                    wire:click="confirmForceDelete"
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                >
                                    {{ __('Eliminar permanentemente') }}
                                </flux:button>
                            @endcan
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-event" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Evento') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar este evento?') }}
                <br>
                <strong>{{ $event->title }}</strong>
            </flux:text>
            <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Esta acción marcará el evento como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.') }}
            </flux:text>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="danger"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                >
                    <span wire:loading.remove wire:target="delete">
                        {{ __('common.actions.delete') }}
                    </span>
                    <span wire:loading wire:target="delete">
                        {{ __('Eliminando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Restore Confirmation Modal --}}
    <flux:modal name="restore-event" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Evento') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar este evento?') }}
                <br>
                <strong>{{ $event->title }}</strong>
            </flux:text>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showRestoreModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="primary"
                    wire:loading.attr="disabled"
                    wire:target="restore"
                >
                    <span wire:loading.remove wire:target="restore">
                        {{ __('Restaurar') }}
                    </span>
                    <span wire:loading wire:target="restore">
                        {{ __('Restaurando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Confirmation Modal --}}
    <flux:modal name="force-delete-event" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente este evento?') }}
                <br>
                <strong>{{ $event->title }}</strong>
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). El evento se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="danger"
                    wire:loading.attr="disabled"
                    wire:target="forceDelete"
                >
                    <span wire:loading.remove wire:target="forceDelete">
                        {{ __('Eliminar permanentemente') }}
                    </span>
                    <span wire:loading wire:target="forceDelete">
                        {{ __('Eliminando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="event-deleted" variant="success" />
    <x-ui.toast event="event-restored" variant="success" />
    <x-ui.toast event="event-force-deleted" variant="warning" />
    <x-ui.toast event="visibility-toggled" variant="success" />
</div>
