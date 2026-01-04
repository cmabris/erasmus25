<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Eventos Erasmus+') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona los eventos y fechas importantes del sistema') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                {{-- View Mode Selector --}}
                <div class="flex items-center gap-2 rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-800">
                    <button
                        wire:click="changeViewMode('list')"
                        class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="$viewMode === 'list' 
                            ? 'bg-erasmus-500 text-white' 
                            : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700'"
                    >
                        <flux:icon name="table-cells" class="[:where(&)]:size-4" variant="outline" />
                        {{ __('Lista') }}
                    </button>
                    <button
                        wire:click="changeViewMode('calendar')"
                        class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="$viewMode === 'calendar' 
                            ? 'bg-erasmus-500 text-white' 
                            : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700'"
                    >
                        <flux:icon name="calendar" class="[:where(&)]:size-4" variant="outline" />
                        {{ __('Calendario') }}
                    </button>
                </div>

                @if($this->canCreate())
                    <flux:button 
                        href="{{ route('admin.events.create') }}" 
                        variant="primary"
                        wire:navigate
                        icon="plus"
                    >
                        {{ __('Crear Evento') }}
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Eventos Erasmus+'), 'icon' => 'calendar'],
            ]"
        />
    </div>

    @if($viewMode === 'list')
        {{-- Filters and Search --}}
        <div class="mb-6 animate-fade-in" style="animation-delay: 0.1s;">
            <x-ui.card>
                <div class="space-y-4">
                    {{-- First Row: Search and Reset --}}
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        {{-- Search --}}
                        <div class="flex-1">
                            <x-ui.search-input 
                                wire:model.live.debounce.300ms="search"
                                :placeholder="__('Buscar por título o descripción...')"
                            />
                        </div>

                        {{-- Reset Filters --}}
                        <div>
                            <flux:button 
                                variant="ghost" 
                                wire:click="resetFilters"
                                icon="arrow-path"
                            >
                                {{ __('common.actions.reset') }}
                            </flux:button>
                        </div>
                    </div>

                    {{-- Second Row: Filters --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                        {{-- Program Filter --}}
                        <flux:field>
                            <flux:label>{{ __('Programa') }}</flux:label>
                            <select wire:model.live="programFilter" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todos') }}</option>
                                @foreach($this->availablePrograms as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </flux:field>

                        {{-- Call Filter --}}
                        <flux:field>
                            <flux:label>{{ __('Convocatoria') }}</flux:label>
                            <select wire:model.live="callFilter" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" :disabled="!$programFilter">
                                <option value="">{{ __('Todas') }}</option>
                                @foreach($this->availableCalls as $call)
                                    <option value="{{ $call->id }}">{{ $call->title }}</option>
                                @endforeach
                            </select>
                        </flux:field>

                        {{-- Event Type Filter --}}
                        <flux:field>
                            <flux:label>{{ __('Tipo de Evento') }}</flux:label>
                            <select wire:model.live="eventTypeFilter" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todos') }}</option>
                                @foreach($this->eventTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </flux:field>

                        {{-- Date Filter --}}
                        <flux:field>
                            <flux:label>{{ __('Fecha') }}</flux:label>
                            <input 
                                type="date" 
                                wire:model.live="dateFilter"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                            />
                        </flux:field>

                        {{-- Show Deleted Filter --}}
                        @if($this->canViewDeleted())
                            <flux:field>
                                <flux:label>{{ __('Mostrar eliminados') }}</flux:label>
                                <select wire:model.live="showDeleted" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                    <option value="0">{{ __('No') }}</option>
                                    <option value="1">{{ __('Sí') }}</option>
                                </select>
                            </flux:field>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Loading State --}}
        <div wire:loading.delay wire:target="search,sortBy,updatedShowDeleted,updatedProgramFilter,updatedCallFilter,updatedEventTypeFilter,updatedDateFilter" class="mb-6">
            <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                    <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                    <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
                </div>
            </div>
        </div>

        {{-- Events Table --}}
        <div wire:loading.remove.delay wire:target="search,sortBy,updatedShowDeleted,updatedProgramFilter,updatedCallFilter,updatedEventTypeFilter,updatedDateFilter" class="animate-fade-in" style="animation-delay: 0.2s;">
            @if($this->events->isEmpty())
                <x-ui.empty-state 
                    :title="__('No hay eventos')"
                    :description="__('No se encontraron eventos que coincidan con los filtros aplicados.')"
                    icon="calendar"
                    :action="__('Crear Evento')"
                    :actionHref="route('admin.events.create')"
                    actionIcon="plus"
                />
            @else
                <x-ui.card>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Imagen') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        <button 
                                            wire:click="sortBy('title')" 
                                            class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                        >
                                            {{ __('Título') }}
                                            @if($sortField === 'title')
                                                <flux:icon 
                                                    :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                    class="[:where(&)]:size-4" 
                                                    variant="outline" 
                                                />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Tipo') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Programa') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Convocatoria') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        <button 
                                            wire:click="sortBy('start_date')" 
                                            class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                        >
                                            {{ __('Fecha Inicio') }}
                                            @if($sortField === 'start_date')
                                                <flux:icon 
                                                    :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                    class="[:where(&)]:size-4" 
                                                    variant="outline" 
                                                />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Fecha Fin') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Ubicación') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Público') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Estado') }}
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Acciones') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($this->events as $event)
                                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php
                                                $image = $event->getFirstMediaUrl('images', 'thumbnail') 
                                                    ?? $event->getFirstMediaUrl('images') 
                                                    ?? null;
                                            @endphp
                                            @if($image)
                                                <img 
                                                    src="{{ $image }}" 
                                                    alt="{{ $event->title }}"
                                                    class="h-12 w-12 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                                    loading="lazy"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                />
                                                <div class="hidden h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                                    <flux:icon name="photo" class="[:where(&)]:size-6 text-zinc-400" variant="outline" />
                                                </div>
                                            @else
                                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                                    <flux:icon name="calendar" class="[:where(&)]:size-6 text-zinc-400" variant="outline" />
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                    {{ $event->title }}
                                                </div>
                                                @if($event->trashed())
                                                    <x-ui.badge variant="danger" size="sm">
                                                        {{ __('Eliminado') }}
                                                    </x-ui.badge>
                                                @endif
                                            </div>
                                            @if($event->description)
                                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                    {{ \Illuminate\Support\Str::limit($event->description, 60) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php
                                                $typeConfig = $this->getEventTypeConfig($event->event_type);
                                            @endphp
                                            <x-ui.badge :variant="$typeConfig['variant']" size="sm" :icon="$typeConfig['icon']">
                                                {{ $typeConfig['label'] }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                            @if($event->program)
                                                <flux:tooltip content="{{ __('Ver programa') }}" position="top">
                                                    <a href="{{ route('admin.programs.show', $event->program) }}" wire:navigate class="hover:text-erasmus-600 dark:hover:text-erasmus-400">
                                                        {{ $event->program->name }}
                                                    </a>
                                                </flux:tooltip>
                                            @else
                                                <span class="text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                            @if($event->call)
                                                <flux:tooltip content="{{ __('Ver convocatoria') }}" position="top">
                                                    <a href="{{ route('admin.calls.show', $event->call) }}" wire:navigate class="hover:text-erasmus-600 dark:hover:text-erasmus-400">
                                                        {{ \Illuminate\Support\Str::limit($event->call->title, 30) }}
                                                    </a>
                                                </flux:tooltip>
                                            @else
                                                <span class="text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            <div class="font-medium text-zinc-900 dark:text-white">
                                                {{ $event->start_date->format('d/m/Y') }}
                                            </div>
                                            <div class="text-xs">
                                                {{ $event->start_date->format('H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            @if($event->end_date)
                                                <div class="font-medium text-zinc-900 dark:text-white">
                                                    {{ $event->end_date->format('d/m/Y') }}
                                                </div>
                                                <div class="text-xs">
                                                    {{ $event->end_date->format('H:i') }}
                                                </div>
                                            @else
                                                <span class="text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            @if($event->location)
                                                <div class="max-w-xs truncate" title="{{ $event->location }}">
                                                    {{ $event->location }}
                                                </div>
                                            @else
                                                <span class="text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <x-ui.badge :variant="$event->is_public ? 'success' : 'neutral'" size="sm">
                                                {{ $event->is_public ? __('Sí') : __('No') }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php
                                                $statusConfig = $this->getEventStatusConfig($event);
                                            @endphp
                                            <x-ui.badge :variant="$statusConfig['variant']" size="sm">
                                                {{ $statusConfig['label'] }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                {{-- View --}}
                                                <flux:button 
                                                    href="{{ route('admin.events.show', $event) }}" 
                                                    variant="ghost" 
                                                    size="sm"
                                                    wire:navigate
                                                    icon="eye"
                                                    :label="__('common.actions.view')"
                                                    :tooltip="__('Ver detalles del evento')"
                                                />

                                                @if(!$event->trashed())
                                                    {{-- Edit --}}
                                                    @can('update', $event)
                                                        <flux:button 
                                                            href="{{ route('admin.events.edit', $event) }}" 
                                                            variant="ghost" 
                                                            size="sm"
                                                            wire:navigate
                                                            icon="pencil"
                                                            :label="__('common.actions.edit')"
                                                            :tooltip="__('Editar evento')"
                                                        />
                                                    @endcan

                                                    {{-- Delete --}}
                                                    @can('delete', $event)
                                                        <flux:button 
                                                            wire:click="confirmDelete({{ $event->id }})" 
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="trash"
                                                            :label="__('common.actions.delete')"
                                                            :tooltip="__('Eliminar evento')"
                                                        />
                                                    @endcan
                                                @else
                                                    {{-- Restore --}}
                                                    @can('restore', $event)
                                                        <flux:button 
                                                            wire:click="confirmRestore({{ $event->id }})" 
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="arrow-path"
                                                            :label="__('Restaurar')"
                                                            :tooltip="__('Restaurar evento eliminado')"
                                                        />
                                                    @endcan

                                                    {{-- Force Delete --}}
                                                    @can('forceDelete', $event)
                                                        <flux:button 
                                                            wire:click="confirmForceDelete({{ $event->id }})" 
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="trash"
                                                            :label="__('Eliminar permanentemente')"
                                                            :tooltip="__('Eliminar permanentemente del sistema')"
                                                        />
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $this->events->links() }}
                    </div>
                </x-ui.card>
            @endif
        </div>
    @else
        {{-- Calendar View --}}
        <div class="space-y-6 animate-fade-in" style="animation-delay: 0.1s;">
            {{-- Calendar Navigation and Filters --}}
            <x-ui.card>
                <div class="space-y-4">
                    {{-- Calendar Navigation --}}
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <button 
                                wire:click="previous"
                                type="button"
                                class="rounded-lg border border-zinc-300 bg-white p-2 text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                            >
                                <flux:icon name="chevron-left" class="[:where(&)]:size-5" variant="outline" />
                            </button>
                            
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                                    @if($calendarView === 'month')
                                        {{ $this->currentDateCarbon->translatedFormat('F Y') }}
                                    @elseif($calendarView === 'week')
                                        {{ __('Semana del') }} {{ $this->currentDateCarbon->copy()->startOfWeek()->translatedFormat('d F Y') }}
                                    @else
                                        {{ $this->currentDateCarbon->translatedFormat('l, d F Y') }}
                                    @endif
                                </h2>
                            </div>
                            
                            <button 
                                wire:click="next"
                                type="button"
                                class="rounded-lg border border-zinc-300 bg-white p-2 text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                            >
                                <flux:icon name="chevron-right" class="[:where(&)]:size-5" variant="outline" />
                            </button>
                            
                            <button 
                                wire:click="goToToday"
                                type="button"
                                class="rounded-lg border border-erasmus-300 bg-erasmus-50 px-4 py-2 text-sm font-medium text-erasmus-700 transition-colors hover:bg-erasmus-100 dark:border-erasmus-600 dark:bg-erasmus-900/30 dark:text-erasmus-300 dark:hover:bg-erasmus-900/50"
                            >
                                {{ __('Hoy') }}
                            </button>
                        </div>
                        
                        {{-- View Mode Selector (Month/Week/Day) --}}
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Vista') }}</label>
                            <div class="inline-flex rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-700">
                                <button
                                    wire:click="changeCalendarView('month')"
                                    type="button"
                                    class="rounded px-3 py-1.5 text-sm font-medium transition-colors {{ $calendarView === 'month' 
                                        ? 'bg-erasmus-600 text-white' 
                                        : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600' 
                                    }}"
                                >
                                    {{ __('Mes') }}
                                </button>
                                <button
                                    wire:click="changeCalendarView('week')"
                                    type="button"
                                    class="rounded px-3 py-1.5 text-sm font-medium transition-colors {{ $calendarView === 'week' 
                                        ? 'bg-erasmus-600 text-white' 
                                        : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600' 
                                    }}"
                                >
                                    {{ __('Semana') }}
                                </button>
                                <button
                                    wire:click="changeCalendarView('day')"
                                    type="button"
                                    class="rounded px-3 py-1.5 text-sm font-medium transition-colors {{ $calendarView === 'day' 
                                        ? 'bg-erasmus-600 text-white' 
                                        : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600' 
                                    }}"
                                >
                                    {{ __('Día') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Calendar Filters --}}
                    <div class="flex flex-wrap items-center gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        {{-- Program Filter --}}
                        <div class="flex items-center gap-2">
                            <label for="calendar-program-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('Programa') }}
                            </label>
                            <select 
                                id="calendar-program-filter"
                                wire:model.live="programFilter"
                                class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                @foreach($this->availablePrograms as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Event Type Filter --}}
                        <div class="flex items-center gap-2">
                            <label for="calendar-type-filter" class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('Tipo') }}
                            </label>
                            <select 
                                id="calendar-type-filter"
                                wire:model.live="eventTypeFilter"
                                class="rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                @foreach($this->eventTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Reset Filters --}}
                        @if($programFilter || $eventTypeFilter)
                            <button 
                                wire:click="resetFilters"
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                            >
                                <flux:icon name="x-mark" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.actions.reset') }}
                            </button>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Calendar Content --}}
            @if($calendarView === 'month')
                {{-- Month View --}}
                <x-ui.card class="overflow-hidden p-0">
                    {{-- Weekday Headers --}}
                    <div class="grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-700">
                        @foreach(['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $day)
                            <div class="border-r border-zinc-200 px-4 py-3 text-center text-sm font-semibold text-zinc-700 last:border-r-0 dark:border-zinc-700 dark:text-zinc-300">
                                {{ $day }}
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Calendar Grid --}}
                    <div class="grid grid-cols-7">
                        @foreach($this->calendarDays as $day)
                            <div @class([
                                'min-h-[120px] border-r border-b border-zinc-200 p-2 dark:border-zinc-700',
                                'bg-zinc-50 dark:bg-zinc-900/50' => !$day['isCurrentMonth'],
                                'bg-white dark:bg-zinc-800' => $day['isCurrentMonth'],
                            ])>
                                <div class="mb-2 flex items-center justify-between">
                                    <span @class([
                                        'text-sm font-medium',
                                        'text-zinc-400 dark:text-zinc-600' => !$day['isCurrentMonth'],
                                        'text-zinc-900 dark:text-white' => $day['isCurrentMonth'] && !$day['isToday'],
                                        'rounded-full bg-erasmus-600 px-2 py-0.5 text-white' => $day['isToday'],
                                    ])>
                                        {{ $day['date']->format('d') }}
                                    </span>
                                    @if($day['eventsCount'] > 0)
                                        <span class="rounded-full bg-erasmus-100 px-2 py-0.5 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                                            {{ $day['eventsCount'] }}
                                        </span>
                                    @endif
                                </div>
                                
                                {{-- Events List --}}
                                <div class="space-y-1">
                                    @php
                                        $dayEvents = $day['events'];
                                        if (!($dayEvents instanceof \Illuminate\Support\Collection)) {
                                            $dayEvents = collect($dayEvents);
                                        }
                                        $dayEvents = $dayEvents->take(3);
                                    @endphp
                                    @foreach($dayEvents as $event)
                                        @php
                                            $eventTypeConfig = $this->getEventTypeConfig($event->event_type);
                                            $startTime = $event->start_date->format('H:i');
                                        @endphp
                                        <a 
                                            href="{{ route('admin.events.show', $event) }}"
                                            wire:navigate
                                            class="block truncate rounded px-1.5 py-0.5 text-xs transition-colors hover:opacity-80"
                                            @class([
                                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $eventTypeConfig['variant'] === 'success',
                                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $eventTypeConfig['variant'] === 'danger',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $eventTypeConfig['variant'] === 'info',
                                                'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' => $eventTypeConfig['variant'] === 'warning',
                                                'bg-erasmus-100 text-erasmus-800 dark:bg-erasmus-900/30 dark:text-erasmus-300' => $eventTypeConfig['variant'] === 'primary',
                                                'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' => $eventTypeConfig['variant'] === 'neutral',
                                            ])
                                        >
                                            @if($startTime && $startTime !== '00:00')
                                                {{ $startTime }} 
                                            @endif
                                            {{ \Illuminate\Support\Str::limit($event->title, 20) }}
                                        </a>
                                    @endforeach
                                    @if($day['eventsCount'] > 3)
                                        <button 
                                            wire:click="$wire.goToDate('{{ $day['date']->format('Y-m-d') }}'); $wire.changeCalendarView('day')"
                                            class="block w-full text-left text-xs text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300"
                                        >
                                            +{{ $day['eventsCount'] - 3 }} {{ __('más') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
                
            @elseif($calendarView === 'week')
                {{-- Week View --}}
                <div class="space-y-6">
                    @foreach($this->weekDays as $day)
                        <x-ui.card>
                            <div @class([
                                'border-b px-6 py-4 mb-4',
                                'bg-erasmus-50 dark:bg-erasmus-900/20' => $day['isToday'],
                                'border-zinc-200 dark:border-zinc-700' => !$day['isToday'],
                            ])>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                    {{ $day['date']->translatedFormat('l, d F Y') }}
                                    @if($day['isToday'])
                                        <x-ui.badge variant="primary" size="sm" class="ml-2">
                                            {{ __('Hoy') }}
                                        </x-ui.badge>
                                    @endif
                                </h3>
                            </div>
                            
                            <div class="px-6 pb-6">
                                @if($day['events']->isEmpty())
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('No hay eventos para este día') }}
                                    </p>
                                @else
                                    <div class="space-y-3">
                                        @foreach($day['events'] as $event)
                                            <div class="flex items-start gap-4 rounded-lg border border-zinc-200 p-4 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50">
                                                <div class="flex-shrink-0">
                                                    @php
                                                        $image = $event->getFirstMediaUrl('images', 'thumbnail') 
                                                            ?? $event->getFirstMediaUrl('images') 
                                                            ?? null;
                                                    @endphp
                                                    @if($image)
                                                        <img 
                                                            src="{{ $image }}" 
                                                            alt="{{ $event->title }}"
                                                            class="h-16 w-16 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                                            loading="lazy"
                                                        />
                                                    @else
                                                        <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                                            <flux:icon name="calendar" class="[:where(&)]:size-6 text-zinc-400" variant="outline" />
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div>
                                                            <h4 class="font-semibold text-zinc-900 dark:text-white">
                                                                <a href="{{ route('admin.events.show', $event) }}" wire:navigate class="hover:text-erasmus-600 dark:hover:text-erasmus-400">
                                                                    {{ $event->title }}
                                                                </a>
                                                            </h4>
                                                            <div class="mt-1 flex items-center gap-2">
                                                                @php
                                                                    $typeConfig = $this->getEventTypeConfig($event->event_type);
                                                                @endphp
                                                                <x-ui.badge :variant="$typeConfig['variant']" size="sm" :icon="$typeConfig['icon']">
                                                                    {{ $typeConfig['label'] }}
                                                                </x-ui.badge>
                                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                                                    {{ $event->start_date->format('H:i') }}
                                                                    @if($event->end_date)
                                                                        - {{ $event->end_date->format('H:i') }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <flux:button 
                                                                href="{{ route('admin.events.show', $event) }}" 
                                                                variant="ghost" 
                                                                size="sm"
                                                                wire:navigate
                                                                icon="eye"
                                                                :tooltip="__('Ver detalles')"
                                                            />
                                                            @can('update', $event)
                                                                <flux:button 
                                                                    href="{{ route('admin.events.edit', $event) }}" 
                                                                    variant="ghost" 
                                                                    size="sm"
                                                                    wire:navigate
                                                                    icon="pencil"
                                                                    :tooltip="__('Editar')"
                                                                />
                                                            @endcan
                                                        </div>
                                                    </div>
                                                    @if($event->description)
                                                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                                            {{ $event->description }}
                                                        </p>
                                                    @endif
                                                    @if($event->location)
                                                        <div class="mt-2 flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400">
                                                            <flux:icon name="map-pin" class="[:where(&)]:size-4" variant="outline" />
                                                            {{ $event->location }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
                
            @else
                {{-- Day View --}}
                <x-ui.card>
                    <div class="border-b border-zinc-200 bg-erasmus-50 px-6 py-4 dark:border-zinc-700 dark:bg-erasmus-900/20">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $this->currentDateCarbon->translatedFormat('l, d F Y') }}
                            <x-ui.badge variant="primary" size="sm" class="ml-2">
                                @if($this->currentDateCarbon->isToday())
                                    {{ __('Hoy') }}
                                @else
                                    {{ __('Día seleccionado') }}
                                @endif
                            </x-ui.badge>
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        @if($this->dayEvents->isEmpty())
                            <x-ui.empty-state 
                                :title="__('No hay eventos')"
                                :description="__('No hay eventos programados para este día.')"
                                icon="calendar"
                                :action="__('Crear Evento')"
                                :actionHref="route('admin.events.create')"
                                actionIcon="plus"
                            />
                        @else
                            <div class="space-y-4">
                                @foreach($this->dayEvents as $event)
                                    <div class="flex items-start gap-4 rounded-lg border border-zinc-200 p-4 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50">
                                        <div class="flex-shrink-0">
                                            @php
                                                $image = $event->getFirstMediaUrl('images', 'thumbnail') 
                                                    ?? $event->getFirstMediaUrl('images') 
                                                    ?? null;
                                            @endphp
                                            @if($image)
                                                <img 
                                                    src="{{ $image }}" 
                                                    alt="{{ $event->title }}"
                                                    class="h-20 w-20 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                                    loading="lazy"
                                                />
                                            @else
                                                <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                                    <flux:icon name="calendar" class="[:where(&)]:size-8 text-zinc-400" variant="outline" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <h4 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                                        <a href="{{ route('admin.events.show', $event) }}" wire:navigate class="hover:text-erasmus-600 dark:hover:text-erasmus-400">
                                                            {{ $event->title }}
                                                        </a>
                                                    </h4>
                                                    <div class="mt-2 flex items-center gap-3">
                                                        @php
                                                            $typeConfig = $this->getEventTypeConfig($event->event_type);
                                                        @endphp
                                                        <x-ui.badge :variant="$typeConfig['variant']" size="sm" :icon="$typeConfig['icon']">
                                                            {{ $typeConfig['label'] }}
                                                        </x-ui.badge>
                                                        <div class="flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400">
                                                            <flux:icon name="clock" class="[:where(&)]:size-4" variant="outline" />
                                                            {{ $event->start_date->format('H:i') }}
                                                            @if($event->end_date)
                                                                - {{ $event->end_date->format('H:i') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <flux:button 
                                                        href="{{ route('admin.events.show', $event) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="eye"
                                                        :tooltip="__('Ver detalles')"
                                                    />
                                                    @can('update', $event)
                                                        <flux:button 
                                                            href="{{ route('admin.events.edit', $event) }}" 
                                                            variant="ghost" 
                                                            size="sm"
                                                            wire:navigate
                                                            icon="pencil"
                                                            :tooltip="__('Editar')"
                                                        />
                                                    @endcan
                                                </div>
                                            </div>
                                            @if($event->description)
                                                <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">
                                                    {{ $event->description }}
                                                </p>
                                            @endif
                                            @if($event->location)
                                                <div class="mt-3 flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400">
                                                    <flux:icon name="map-pin" class="[:where(&)]:size-4" variant="outline" />
                                                    {{ $event->location }}
                                                </div>
                                            @endif
                                            @if($event->program || $event->call)
                                                <div class="mt-3 flex items-center gap-4 text-sm">
                                                    @if($event->program)
                                                        <div class="text-zinc-500 dark:text-zinc-400">
                                                            <span class="font-medium">{{ __('Programa') }}:</span>
                                                            <a href="{{ route('admin.programs.show', $event->program) }}" wire:navigate class="text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400">
                                                                {{ $event->program->name }}
                                                            </a>
                                                        </div>
                                                    @endif
                                                    @if($event->call)
                                                        <div class="text-zinc-500 dark:text-zinc-400">
                                                            <span class="font-medium">{{ __('Convocatoria') }}:</span>
                                                            <a href="{{ route('admin.calls.show', $event->call) }}" wire:navigate class="text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400">
                                                                {{ $event->call->title }}
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @endif
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-event" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Evento') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar este evento?') }}
                @if($eventToDelete)
                    <br>
                    <strong>{{ \App\Models\ErasmusEvent::find($eventToDelete)?->title }}</strong>
                @endif
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
                @if($eventToRestore)
                    <br>
                    <strong>{{ \App\Models\ErasmusEvent::onlyTrashed()->find($eventToRestore)?->title }}</strong>
                @endif
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
                @if($eventToForceDelete)
                    <br>
                    <strong>{{ \App\Models\ErasmusEvent::onlyTrashed()->find($eventToForceDelete)?->title }}</strong>
                @endif
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
</div>
