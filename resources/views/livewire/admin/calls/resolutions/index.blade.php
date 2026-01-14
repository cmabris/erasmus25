<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Resoluciones de Convocatoria') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona las resoluciones de la convocatoria: :title', ['title' => $call->title]) }}
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
                        href="{{ route('admin.calls.resolutions.create', $call) }}" 
                        variant="primary"
                        wire:navigate
                        icon="plus"
                    >
                        {{ __('Crear Resolución') }}
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('common.nav.calls'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
                ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'megaphone'],
                ['label' => __('common.nav.resolutions'), 'icon' => 'document-check'],
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
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total de Resoluciones') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->resolutions()->count() }}</p>
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Type Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Tipo de Resolución') }}</flux:label>
                        <select wire:model.live="filterType" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->getTypeOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Published Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Estado de Publicación') }}</flux:label>
                        <select wire:model.live="filterPublished" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todas') }}</option>
                            <option value="1">{{ __('Publicadas') }}</option>
                            <option value="0">{{ __('No publicadas') }}</option>
                        </select>
                    </flux:field>

                    {{-- Phase Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Fase') }}</flux:label>
                        <select wire:model.live="filterPhase" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todas') }}</option>
                            @foreach($this->callPhases as $phase)
                                <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                            @endforeach
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
    <div wire:loading.delay wire:target="search,sortBy,updatedFilterType,updatedFilterPublished,updatedFilterPhase,updatedShowDeleted" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Resolutions Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedFilterType,updatedFilterPublished,updatedFilterPhase,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->resolutions->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay resoluciones')"
                :description="__('No se encontraron resoluciones que coincidan con los filtros aplicados.')"
                icon="document-check"
                :action="__('Crear Resolución')"
                :actionHref="route('admin.calls.resolutions.create', $call)"
                actionIcon="plus"
            />
        @else
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('type')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Tipo') }}
                                        @if($sortField === 'type')
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
                                    {{ __('Fase') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('official_date')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Fecha Oficial') }}
                                        @if($sortField === 'official_date')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Publicación') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('PDF') }}
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($this->resolutions as $resolution)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-ui.badge :variant="$this->getTypeColor($resolution->type)" size="sm">
                                            {{ $this->getTypeOptions()[$resolution->type] ?? $resolution->type }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $resolution->title }}
                                            </div>
                                            @if($resolution->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                        @if($resolution->description)
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                {{ \Illuminate\Support\Str::limit($resolution->description, 50) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $resolution->callPhase->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $resolution->official_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($resolution->published_at)
                                            <x-ui.badge variant="success" size="sm">
                                                {{ __('Publicada') }}
                                            </x-ui.badge>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $resolution->published_at->format('d/m/Y') }}
                                            </p>
                                        @else
                                            <span class="text-sm text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($this->hasPdf($resolution))
                                            <flux:icon name="document-text" class="[:where(&)]:size-5 text-green-600 dark:text-green-400" variant="outline" />
                                        @else
                                            <span class="text-sm text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.calls.resolutions.show', [$call, $resolution]) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles de la resolución')"
                                            />

                                            @if(!$resolution->trashed())
                                                {{-- Publish/Unpublish --}}
                                                @can('publish', $resolution)
                                                    @if($resolution->published_at)
                                                        <flux:button 
                                                            wire:click="unpublish({{ $resolution->id }})"
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="eye-slash"
                                                            :label="__('Despublicar')"
                                                            :tooltip="__('Despublicar resolución')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="unpublish({{ $resolution->id }})"
                                                        />
                                                    @else
                                                        <flux:button 
                                                            wire:click="publish({{ $resolution->id }})"
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="paper-airplane"
                                                            :label="__('Publicar')"
                                                            :tooltip="__('Publicar resolución')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="publish({{ $resolution->id }})"
                                                        />
                                                    @endif
                                                @endcan

                                                {{-- Edit --}}
                                                @can('update', $resolution)
                                                    <flux:button 
                                                        href="{{ route('admin.calls.resolutions.edit', [$call, $resolution]) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar resolución')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @can('delete', $resolution)
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $resolution->id }})"
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('common.actions.delete')"
                                                        :tooltip="__('Eliminar resolución')"
                                                    />
                                                @endcan
                                            @else
                                                {{-- Restore --}}
                                                @can('restore', $resolution)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $resolution->id }})"
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('common.actions.restore')"
                                                        :tooltip="__('Restaurar resolución')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $resolution)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $resolution->id }})"
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
                    {{ $this->resolutions->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-resolution" :show="$showDeleteModal" wire:model="showDeleteModal">
        <form wire:submit="delete" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Resolución') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar esta resolución? Esta acción puede revertirse.') }}
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
    <flux:modal name="restore-resolution" :show="$showRestoreModal" wire:model="showRestoreModal">
        <form wire:submit="restore" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Restaurar Resolución') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas restaurar esta resolución?') }}
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
    <flux:modal name="force-delete-resolution" :show="$showForceDeleteModal" wire:model="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Permanentemente') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar permanentemente esta resolución? Esta acción no se puede revertir.') }}
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
    <x-ui.toast event="resolution-published" variant="success" />
    <x-ui.toast event="resolution-unpublished" variant="success" />
    <x-ui.toast event="resolution-deleted" variant="success" />
    <x-ui.toast event="resolution-restored" variant="success" />
    <x-ui.toast event="resolution-force-deleted" variant="success" />
</div>
