<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Convocatorias') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona las convocatorias Erasmus+ disponibles en el sistema') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.calls.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Convocatoria') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Convocatorias'), 'icon' => 'document-text'],
            ]"
        />
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
                            :placeholder="__('Buscar por título o slug...')"
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    {{-- Program Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Programa') }}</flux:label>
                        <select wire:model.live="filterProgram" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Academic Year Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Año Académico') }}</flux:label>
                        <select wire:model.live="filterAcademicYear" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}">{{ $academicYear->year }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Type Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Tipo') }}</flux:label>
                        <select wire:model.live="filterType" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            <option value="alumnado">{{ __('Alumnado') }}</option>
                            <option value="personal">{{ __('Personal') }}</option>
                        </select>
                    </flux:field>

                    {{-- Modality Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Modalidad') }}</flux:label>
                        <select wire:model.live="filterModality" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todas') }}</option>
                            <option value="corta">{{ __('Corta') }}</option>
                            <option value="larga">{{ __('Larga') }}</option>
                        </select>
                    </flux:field>

                    {{-- Status Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Estado') }}</flux:label>
                        <select wire:model.live="filterStatus" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            <option value="borrador">{{ __('Borrador') }}</option>
                            <option value="abierta">{{ __('Abierta') }}</option>
                            <option value="cerrada">{{ __('Cerrada') }}</option>
                            <option value="en_baremacion">{{ __('En Baremación') }}</option>
                            <option value="resuelta">{{ __('Resuelta') }}</option>
                            <option value="archivada">{{ __('Archivada') }}</option>
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
    <div wire:loading.delay wire:target="search,sortBy,updatedFilterProgram,updatedFilterAcademicYear,updatedFilterType,updatedFilterModality,updatedFilterStatus,updatedShowDeleted" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Calls Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedFilterProgram,updatedFilterAcademicYear,updatedFilterType,updatedFilterModality,updatedFilterStatus,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->calls->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay convocatorias')"
                :description="__('No se encontraron convocatorias que coincidan con los filtros aplicados.')"
                icon="document-text"
                :action="__('Crear Convocatoria')"
                :actionHref="route('admin.calls.create')"
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
                                    {{ __('Programa') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Año Académico') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Tipo') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Modalidad') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('status')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Estado') }}
                                        @if($sortField === 'status')
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
                                        wire:click="sortBy('published_at')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Publicación') }}
                                        @if($sortField === 'published_at')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($this->calls as $call)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $call->title }}
                                            </div>
                                            @if($call->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $call->program->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $call->academicYear->year ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-ui.badge variant="info" size="sm">
                                            {{ $call->type === 'alumnado' ? __('Alumnado') : __('Personal') }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-ui.badge variant="secondary" size="sm">
                                            {{ $call->modality === 'corta' ? __('Corta') : __('Larga') }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-ui.badge :variant="$this->getStatusColor($call->status)" size="sm">
                                            {{ ucfirst(str_replace('_', ' ', $call->status)) }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($call->published_at)
                                            {{ $call->published_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.calls.show', $call) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles de la convocatoria')"
                                            />

                                            @if(!$call->trashed())
                                                {{-- Publish --}}
                                                @can('publish', $call)
                                                    @if($call->status !== 'abierta')
                                                        <flux:button 
                                                            wire:click="publish({{ $call->id }})"
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="paper-airplane"
                                                            :label="__('Publicar')"
                                                            :tooltip="__('Publicar convocatoria')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="publish({{ $call->id }})"
                                                        />
                                                    @endif
                                                @endcan

                                                {{-- Change Status Dropdown --}}
                                                @can('update', $call)
                                                    <div class="relative" x-data="{ open: false }">
                                                        <flux:button 
                                                            @click="open = !open"
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="adjustments-horizontal"
                                                            :label="__('Estado')"
                                                            :tooltip="__('Cambiar estado')"
                                                        />
                                                        <div 
                                                            x-show="open"
                                                            @click.away="open = false"
                                                            x-transition
                                                            class="absolute right-0 mt-2 w-48 rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800 z-50"
                                                        >
                                                            <div class="p-1">
                                                                @foreach(['borrador', 'abierta', 'cerrada', 'en_baremacion', 'resuelta', 'archivada'] as $status)
                                                                    @if($status !== $call->status)
                                                                        <button
                                                                            wire:click="changeStatus({{ $call->id }}, '{{ $status }}')"
                                                                            class="w-full text-left px-3 py-2 text-sm rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300"
                                                                        >
                                                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                                        </button>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcan

                                                {{-- Edit --}}
                                                @can('update', $call)
                                                    <flux:button 
                                                        href="{{ route('admin.calls.edit', $call) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar convocatoria')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @can('delete', $call)
                                                    @php
                                                        $hasRelations = $call->phases_count > 0 
                                                            || $call->resolutions_count > 0 
                                                            || $call->applications_count > 0;
                                                    @endphp
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $call->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('common.actions.delete')"
                                                        :disabled="$hasRelations"
                                                        :tooltip="$hasRelations ? __('common.errors.cannot_delete_with_relations') : __('Eliminar convocatoria')"
                                                    />
                                                @endcan
                                            @else
                                                {{-- Restore --}}
                                                @can('restore', $call)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $call->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('Restaurar')"
                                                        :tooltip="__('Restaurar convocatoria eliminada')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $call)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $call->id }})" 
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
                    {{ $this->calls->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-call" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Convocatoria') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta convocatoria?') }}
                @if($callToDelete)
                    @php
                        $call = \App\Models\Call::find($callToDelete);
                        $hasRelations = $call && (
                            $call->phases()->exists() 
                            || $call->resolutions()->exists() 
                            || $call->applications()->exists()
                        );
                    @endphp
                    <br>
                    <strong>{{ $call?->title }}</strong>
                    @if($hasRelations)
                        <br><br>
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            {{ __('No se puede eliminar esta convocatoria porque tiene relaciones activas (fases, resoluciones o aplicaciones).') }}
                        </span>
                    @endif
                @endif
            </flux:text>
            @if($callToDelete)
                @php
                    $call = \App\Models\Call::find($callToDelete);
                    $hasRelations = $call && (
                        $call->phases()->exists() 
                        || $call->resolutions()->exists() 
                        || $call->applications()->exists()
                    );
                @endphp
                @if(!$hasRelations)
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Esta acción marcará la convocatoria como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
                    </flux:text>
                @endif
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará la convocatoria como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($callToDelete)
                    @php
                        $call = \App\Models\Call::find($callToDelete);
                        $hasRelations = $call && (
                            $call->phases()->exists() 
                            || $call->resolutions()->exists() 
                            || $call->applications()->exists()
                        );
                    @endphp
                    @if(!$hasRelations)
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
                    @endif
                @else
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
                @endif
            </div>
        </form>
    </flux:modal>

    {{-- Restore Confirmation Modal --}}
    <flux:modal name="restore-call" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Convocatoria') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar esta convocatoria?') }}
                @if($callToRestore)
                    <br>
                    <strong>{{ \App\Models\Call::onlyTrashed()->find($callToRestore)?->title }}</strong>
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
    <flux:modal name="force-delete-call" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente esta convocatoria?') }}
                @if($callToForceDelete)
                    <br>
                    <strong>{{ \App\Models\Call::onlyTrashed()->find($callToForceDelete)?->title }}</strong>
                @endif
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). La convocatoria se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($callToForceDelete)
                @php
                    $call = \App\Models\Call::onlyTrashed()->find($callToForceDelete);
                    $hasRelations = $call && (
                        $call->phases()->exists() 
                        || $call->resolutions()->exists() 
                        || $call->applications()->exists()
                    );
                @endphp
                @if($hasRelations)
                    <flux:callout variant="warning" class="mt-4">
                        <flux:callout.text>
                            {{ __('No se puede eliminar permanentemente esta convocatoria porque tiene relaciones activas (fases, resoluciones o aplicaciones). Primero debes eliminar o reasignar estas relaciones.') }}
                        </flux:callout.text>
                    </flux:callout>
                @endif
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($callToForceDelete)
                    @php
                        $call = \App\Models\Call::onlyTrashed()->find($callToForceDelete);
                        $hasRelations = $call && (
                            $call->phases()->exists() 
                            || $call->resolutions()->exists() 
                            || $call->applications()->exists()
                        );
                    @endphp
                    <flux:button 
                        type="submit" 
                        variant="danger"
                        :disabled="$hasRelations"
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
                @else
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
                @endif
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="call-deleted" variant="success" />
    <x-ui.toast event="call-restored" variant="success" />
    <x-ui.toast event="call-force-deleted" variant="warning" />
    <x-ui.toast event="call-force-delete-error" variant="error" />
    <x-ui.toast event="call-delete-error" variant="error" />
    <x-ui.toast event="call-updated" variant="success" />
    <x-ui.toast event="call-published" variant="success" />
</div>
