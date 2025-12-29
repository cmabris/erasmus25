<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Fases de Convocatoria') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona las fases de la convocatoria: :title', ['title' => $call->title]) }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <flux:button 
                    href="{{ route('admin.calls.show', $call) }}" 
                    variant="ghost"
                    wire:navigate
                    icon="arrow-left"
                >
                    {{ __('Volver a Convocatoria') }}
                </flux:button>
                @if($this->canCreate())
                    <flux:button 
                        href="{{ route('admin.calls.phases.create', $call) }}" 
                        variant="primary"
                        wire:navigate
                        icon="plus"
                    >
                        {{ __('Crear Fase') }}
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Convocatorias'), 'href' => route('admin.calls.index'), 'icon' => 'document-text'],
                ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'document'],
                ['label' => __('Fases'), 'icon' => 'list-bullet'],
            ]"
        />
    </div>

    {{-- Call Info Card --}}
    <div class="mb-6 animate-fade-in" style="animation-delay: 0.05s;">
        <x-ui.card>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Programa') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->program->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Año Académico') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->academicYear->year ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Estado') }}</p>
                    <p class="mt-1">
                        <x-ui.badge :variant="$this->getStatusColor($call->status)" size="sm">
                            {{ ucfirst(str_replace('_', ' ', $call->status)) }}
                        </x-ui.badge>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total de Fases') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->phases()->count() }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

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
                            :placeholder="__('Buscar por nombre o descripción...')"
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Phase Type Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Tipo de Fase') }}</flux:label>
                        <select wire:model.live="filterPhaseType" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->getPhaseTypeOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Is Current Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Fase Actual') }}</flux:label>
                        <select wire:model.live="filterIsCurrent" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todas') }}</option>
                            <option value="1">{{ __('Solo actuales') }}</option>
                            <option value="0">{{ __('No actuales') }}</option>
                        </select>
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
    <div wire:loading.delay wire:target="search,sortBy,updatedFilterPhaseType,updatedFilterIsCurrent,updatedShowDeleted" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Phases Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedFilterPhaseType,updatedFilterIsCurrent,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->phases->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay fases')"
                :description="__('No se encontraron fases que coincidan con los filtros aplicados.')"
                icon="list-bullet"
                :action="__('Crear Fase')"
                :actionHref="route('admin.calls.phases.create', $call)"
                actionIcon="plus"
            />
        @else
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Orden') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('phase_type')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Tipo') }}
                                        @if($sortField === 'phase_type')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('name')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Nombre') }}
                                        @if($sortField === 'name')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Fechas') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Estado Actual') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Resoluciones') }}
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($this->phases as $phase)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $phase->order }}
                                            </span>
                                            @if(!$phase->trashed())
                                                <div class="flex flex-col gap-1">
                                                    <flux:button 
                                                        wire:click="moveUp({{ $phase->id }})"
                                                        variant="ghost"
                                                        size="xs"
                                                        icon="chevron-up"
                                                        :tooltip="__('Mover arriba')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="moveUp({{ $phase->id }})"
                                                    />
                                                    <flux:button 
                                                        wire:click="moveDown({{ $phase->id }})"
                                                        variant="ghost"
                                                        size="xs"
                                                        icon="chevron-down"
                                                        :tooltip="__('Mover abajo')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="moveDown({{ $phase->id }})"
                                                    />
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-ui.badge :variant="$this->getPhaseTypeColor($phase->phase_type)" size="sm">
                                            {{ $this->getPhaseTypeOptions()[$phase->phase_type] ?? $phase->phase_type }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $phase->name }}
                                            </div>
                                            @if($phase->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                        @if($phase->description)
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                {{ \Illuminate\Support\Str::limit($phase->description, 50) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($phase->start_date || $phase->end_date)
                                            <div class="flex flex-col">
                                                @if($phase->start_date)
                                                    <span>{{ __('Desde') }}: {{ $phase->start_date->format('d/m/Y') }}</span>
                                                @endif
                                                @if($phase->end_date)
                                                    <span>{{ __('Hasta') }}: {{ $phase->end_date->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($phase->is_current)
                                            <x-ui.badge variant="success" size="sm">
                                                {{ __('Actual') }}
                                            </x-ui.badge>
                                        @else
                                            <span class="text-sm text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $phase->resolutions_count ?? 0 }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.calls.phases.show', [$call, $phase]) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles de la fase')"
                                            />

                                            @if(!$phase->trashed())
                                                {{-- Mark as Current / Unmark --}}
                                                @can('update', $phase)
                                                    @if($phase->is_current)
                                                        <flux:button 
                                                            wire:click="unmarkAsCurrent({{ $phase->id }})"
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="x-mark"
                                                            :label="__('Desmarcar')"
                                                            :tooltip="__('Desmarcar como fase actual')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="unmarkAsCurrent({{ $phase->id }})"
                                                        />
                                                    @else
                                                        <flux:button 
                                                            wire:click="markAsCurrent({{ $phase->id }})"
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="check"
                                                            :label="__('Marcar actual')"
                                                            :tooltip="__('Marcar como fase actual')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="markAsCurrent({{ $phase->id }})"
                                                        />
                                                    @endif
                                                @endcan

                                                {{-- Edit --}}
                                                @can('update', $phase)
                                                    <flux:button 
                                                        href="{{ route('admin.calls.phases.edit', [$call, $phase]) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar fase')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @can('delete', $phase)
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $phase->id }})"
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('common.actions.delete')"
                                                        :tooltip="__('Eliminar fase')"
                                                    />
                                                @endcan
                                            @else
                                                {{-- Restore --}}
                                                @can('restore', $phase)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $phase->id }})"
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('common.actions.restore')"
                                                        :tooltip="__('Restaurar fase')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $phase)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $phase->id }})"
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('common.actions.permanently_delete')"
                                                        :tooltip="__('Eliminar permanentemente')"
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
                <div class="mt-6 border-t border-zinc-200 px-4 py-4 dark:border-zinc-700 sm:px-6">
                    {{ $this->phases->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-phase" :show="$showDeleteModal" wire:model="showDeleteModal">
        <form wire:submit="delete" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Fase') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar esta fase? Esta acción puede revertirse.') }}
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="$set('showDeleteModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="danger"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.delete') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Restore Confirmation Modal --}}
    <flux:modal name="restore-phase" :show="$showRestoreModal" wire:model="showRestoreModal">
        <form wire:submit="restore" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Restaurar Fase') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas restaurar esta fase?') }}
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="$set('showRestoreModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="primary"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.restore') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Confirmation Modal --}}
    <flux:modal name="force-delete-phase" :show="$showForceDeleteModal" wire:model="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Permanentemente') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar permanentemente esta fase? Esta acción no se puede revertir.') }}
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="$set('showForceDeleteModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="danger"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.permanently_delete') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="phase-updated" variant="success" />
    <x-ui.toast event="phase-reordered" variant="success" />
    <x-ui.toast event="phase-reorder-error" variant="danger" />
    <x-ui.toast event="phase-deleted" variant="success" />
    <x-ui.toast event="phase-restored" variant="success" />
    <x-ui.toast event="phase-force-deleted" variant="success" />
    <x-ui.toast event="phase-delete-error" variant="danger" />
    <x-ui.toast event="phase-force-delete-error" variant="danger" />
</div>
