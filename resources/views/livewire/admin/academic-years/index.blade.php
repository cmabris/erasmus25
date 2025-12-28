<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Años Académicos') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona los años académicos disponibles en el sistema') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.academic-years.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Año Académico') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Años Académicos'), 'icon' => 'calendar'],
            ]"
        />
    </div>

    {{-- Filters and Search --}}
    <div class="mb-6 animate-fade-in" style="animation-delay: 0.1s;">
        <x-ui.card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- Search --}}
                <div class="flex-1">
                    <x-ui.search-input 
                        wire:model.live.debounce.300ms="search"
                        :placeholder="__('Buscar por año académico o fechas...')"
                    />
                </div>

                {{-- Show Deleted Filter --}}
                @if($this->canViewDeleted())
                    <div class="sm:w-48">
                        <flux:field>
                            <flux:label>{{ __('Mostrar eliminados') }}</flux:label>
                            <select wire:model.live="showDeleted" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="0" @selected($showDeleted === '0')>{{ __('No') }}</option>
                                <option value="1" @selected($showDeleted === '1')>{{ __('Sí') }}</option>
                            </select>
                        </flux:field>
                    </div>
                @endif

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
        </x-ui.card>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay wire:target="search,sortBy,updatedShowDeleted" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Academic Years Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->academicYears->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay años académicos')"
                :description="__('No se encontraron años académicos que coincidan con los filtros aplicados.')"
                icon="calendar"
                :action="__('Crear Año Académico')"
                :actionHref="route('admin.academic-years.create')"
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
                                        wire:click="sortBy('year')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Año Académico') }}
                                        @if($sortField === 'year')
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
                                    <button 
                                        wire:click="sortBy('end_date')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Fecha Fin') }}
                                        @if($sortField === 'end_date')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Año Actual') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Relaciones') }}
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($this->academicYears as $academicYear)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $academicYear->year }}
                                            </div>
                                            @if($academicYear->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $academicYear->start_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $academicYear->end_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($academicYear->is_current)
                                            <x-ui.badge variant="success" size="sm" icon="star">
                                                {{ __('Año Actual') }}
                                            </x-ui.badge>
                                        @else
                                            <span class="text-sm text-zinc-400 dark:text-zinc-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col gap-1">
                                            <flux:tooltip content="{{ __('Número de convocatorias asociadas a este año académico') }}" position="top">
                                                <span>{{ $academicYear->calls_count }} {{ __('Convocatorias') }}</span>
                                            </flux:tooltip>
                                            <flux:tooltip content="{{ __('Número de noticias asociadas a este año académico') }}" position="top">
                                                <span>{{ $academicYear->news_posts_count }} {{ __('Noticias') }}</span>
                                            </flux:tooltip>
                                            <flux:tooltip content="{{ __('Número de documentos asociados a este año académico') }}" position="top">
                                                <span>{{ $academicYear->documents_count }} {{ __('Documentos') }}</span>
                                            </flux:tooltip>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.academic-years.show', $academicYear) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles del año académico')"
                                            />

                                            @if(!$academicYear->trashed())
                                                {{-- Toggle Current --}}
                                                @can('update', $academicYear)
                                                    <flux:button 
                                                        wire:click="toggleCurrent({{ $academicYear->id }})"
                                                        variant="{{ $academicYear->is_current ? 'primary' : 'ghost' }}" 
                                                        size="sm"
                                                        icon="star"
                                                        :label="$academicYear->is_current ? __('Desmarcar como actual') : __('Marcar como actual')"
                                                        :tooltip="$academicYear->is_current ? __('Desmarcar como año actual') : __('Marcar como año actual')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="toggleCurrent({{ $academicYear->id }})"
                                                    >
                                                        <span wire:loading.remove wire:target="toggleCurrent({{ $academicYear->id }})">
                                                            {{ $academicYear->is_current ? __('Desmarcar') : __('Marcar') }}
                                                        </span>
                                                        <span wire:loading wire:target="toggleCurrent({{ $academicYear->id }})">
                                                            ...
                                                        </span>
                                                    </flux:button>
                                                @endcan

                                                {{-- Edit --}}
                                                @can('update', $academicYear)
                                                    <flux:button 
                                                        href="{{ route('admin.academic-years.edit', $academicYear) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar año académico')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @can('delete', $academicYear)
                                                    @php
                                                        $hasRelations = $academicYear->calls_count > 0 
                                                            || $academicYear->news_posts_count > 0 
                                                            || $academicYear->documents_count > 0;
                                                        $canDelete = $this->canDeleteAcademicYear($academicYear);
                                                        $deleteTooltip = !$canDelete 
                                                            ? __('common.errors.cannot_delete_with_relations') 
                                                            : __('Eliminar año académico');
                                                    @endphp
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $academicYear->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('common.actions.delete')"
                                                        :disabled="!$canDelete"
                                                        :tooltip="$deleteTooltip"
                                                    />
                                                @endcan
                                            @else
                                                {{-- Restore --}}
                                                @can('restore', $academicYear)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $academicYear->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('Restaurar')"
                                                        :tooltip="__('Restaurar año académico eliminado')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $academicYear)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $academicYear->id }})" 
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
                    {{ $this->academicYears->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-academic-year" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Año Académico') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar este año académico?') }}
                @if($academicYearToDelete)
                    @php
                        $academicYear = \App\Models\AcademicYear::find($academicYearToDelete);
                        $hasRelations = $academicYear && (
                            $academicYear->calls()->exists() 
                            || $academicYear->newsPosts()->exists() 
                            || $academicYear->documents()->exists()
                        );
                    @endphp
                    <br>
                    <strong>{{ $academicYear?->year }}</strong>
                    @if($hasRelations)
                        <br><br>
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            {{ __('No se puede eliminar este año académico porque tiene relaciones activas (convocatorias, noticias o documentos).') }}
                        </span>
                    @endif
                @endif
            </flux:text>
            @if($academicYearToDelete)
                @php
                    $academicYear = \App\Models\AcademicYear::find($academicYearToDelete);
                    $hasRelations = $academicYear && (
                        $academicYear->calls()->exists() 
                        || $academicYear->newsPosts()->exists() 
                        || $academicYear->documents()->exists()
                    );
                @endphp
                @if(!$hasRelations)
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Esta acción marcará el año académico como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.') }}
                    </flux:text>
                @endif
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará el año académico como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($academicYearToDelete)
                    @php
                        $academicYear = \App\Models\AcademicYear::find($academicYearToDelete);
                        $canDelete = $academicYear && !(
                            $academicYear->calls()->exists() 
                            || $academicYear->newsPosts()->exists() 
                            || $academicYear->documents()->exists()
                        );
                    @endphp
                    @if($canDelete)
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
    <flux:modal name="restore-academic-year" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Año Académico') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar este año académico?') }}
                @if($academicYearToRestore)
                    <br>
                    <strong>{{ \App\Models\AcademicYear::onlyTrashed()->find($academicYearToRestore)?->year }}</strong>
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
    <flux:modal name="force-delete-academic-year" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente este año académico?') }}
                @if($academicYearToForceDelete)
                    <br>
                    <strong>{{ \App\Models\AcademicYear::onlyTrashed()->find($academicYearToForceDelete)?->year }}</strong>
                @endif
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). El año académico se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($academicYearToForceDelete)
                @php
                    $academicYear = \App\Models\AcademicYear::onlyTrashed()->find($academicYearToForceDelete);
                    $hasRelations = $academicYear && (
                        $academicYear->calls()->exists() 
                        || $academicYear->newsPosts()->exists() 
                        || $academicYear->documents()->exists()
                    );
                @endphp
                @if($hasRelations)
                    <flux:callout variant="warning" class="mt-4">
                        <flux:callout.text>
                            {{ __('No se puede eliminar permanentemente este año académico porque tiene relaciones activas (convocatorias, noticias o documentos). Primero debes eliminar o reasignar estas relaciones.') }}
                        </flux:callout.text>
                    </flux:callout>
                @endif
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($academicYearToForceDelete)
                    @php
                        $academicYear = \App\Models\AcademicYear::onlyTrashed()->find($academicYearToForceDelete);
                        $hasRelations = $academicYear && (
                            $academicYear->calls()->exists() 
                            || $academicYear->newsPosts()->exists() 
                            || $academicYear->documents()->exists()
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
    <x-ui.toast event="academic-year-deleted" variant="success" />
    <x-ui.toast event="academic-year-restored" variant="success" />
    <x-ui.toast event="academic-year-force-deleted" variant="warning" />
    <x-ui.toast event="academic-year-force-delete-error" variant="error" />
    <x-ui.toast event="academic-year-delete-error" variant="error" />
    <x-ui.toast event="academic-year-updated" variant="success" />
</div>
