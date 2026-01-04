<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Categorías de Documentos') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona las categorías disponibles para los documentos') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.document-categories.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Categoría') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Categorías de Documentos'), 'icon' => 'folder'],
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
                        :placeholder="__('Buscar por nombre, slug o descripción...')"
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

    {{-- Document Categories Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->documentCategories->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay categorías')"
                :description="__('No se encontraron categorías que coincidan con los filtros aplicados.')"
                icon="folder"
                :action="__('Crear Categoría')"
                :actionHref="route('admin.document-categories.create')"
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
                                        wire:click="sortBy('order')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Orden') }}
                                        @if($sortField === 'order')
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
                                    <button 
                                        wire:click="sortBy('slug')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Slug') }}
                                        @if($sortField === 'slug')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Descripción') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Documentos') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('created_at')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Fecha Creación') }}
                                        @if($sortField === 'created_at')
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
                            @foreach($this->documentCategories as $documentCategory)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $documentCategory->order ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $documentCategory->name }}
                                            </div>
                                            @if($documentCategory->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $documentCategory->slug }}</code>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($documentCategory->description)
                                            <div class="max-w-xs truncate" title="{{ $documentCategory->description }}">
                                                {{ $documentCategory->description }}
                                            </div>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($documentCategory->documents_count > 0)
                                            <flux:tooltip content="{{ __('Número de documentos asociados a esta categoría') }}" position="top">
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $documentCategory->documents_count }}</span>
                                            </flux:tooltip>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $documentCategory->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.document-categories.show', $documentCategory) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles de la categoría')"
                                            />

                                            @if(!$documentCategory->trashed())
                                                {{-- Edit --}}
                                                @can('update', $documentCategory)
                                                    <flux:button 
                                                        href="{{ route('admin.document-categories.edit', $documentCategory) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar categoría')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @can('delete', $documentCategory)
                                                    @php
                                                        $hasRelations = $documentCategory->documents_count > 0;
                                                        $canDelete = $this->canDeleteDocumentCategory($documentCategory);
                                                        $deleteTooltip = !$canDelete 
                                                            ? __('No se puede eliminar la categoría porque tiene documentos asociados.') 
                                                            : __('Eliminar categoría');
                                                    @endphp
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $documentCategory->id }})" 
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
                                                @can('restore', $documentCategory)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $documentCategory->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('Restaurar')"
                                                        :tooltip="__('Restaurar categoría eliminada')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $documentCategory)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $documentCategory->id }})" 
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
                    {{ $this->documentCategories->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-document-category" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Categoría') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta categoría?') }}
                @if($documentCategoryToDelete)
                    @php
                        $documentCategory = \App\Models\DocumentCategory::find($documentCategoryToDelete);
                        $hasRelations = $documentCategory && $documentCategory->documents()->exists();
                    @endphp
                    <br>
                    <strong>{{ $documentCategory?->name }}</strong>
                    @if($hasRelations)
                        <br><br>
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            {{ __('No se puede eliminar esta categoría porque tiene documentos asociados.') }}
                        </span>
                    @endif
                @endif
            </flux:text>
            @if($documentCategoryToDelete)
                @php
                    $documentCategory = \App\Models\DocumentCategory::find($documentCategoryToDelete);
                    $hasRelations = $documentCategory && $documentCategory->documents()->exists();
                @endphp
                @if(!$hasRelations)
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Esta acción marcará la categoría como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
                    </flux:text>
                @endif
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará la categoría como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($documentCategoryToDelete)
                    @php
                        $documentCategory = \App\Models\DocumentCategory::find($documentCategoryToDelete);
                        $canDelete = $documentCategory && !$documentCategory->documents()->exists();
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
    <flux:modal name="restore-document-category" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Categoría') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar esta categoría?') }}
                @if($documentCategoryToRestore)
                    <br>
                    <strong>{{ \App\Models\DocumentCategory::onlyTrashed()->find($documentCategoryToRestore)?->name }}</strong>
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
    <flux:modal name="force-delete-document-category" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente esta categoría?') }}
                @if($documentCategoryToForceDelete)
                    <br>
                    <strong>{{ \App\Models\DocumentCategory::onlyTrashed()->find($documentCategoryToForceDelete)?->name }}</strong>
                @endif
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). La categoría se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($documentCategoryToForceDelete)
                @php
                    $documentCategory = \App\Models\DocumentCategory::onlyTrashed()->find($documentCategoryToForceDelete);
                    $hasRelations = $documentCategory && $documentCategory->documents()->exists();
                @endphp
                @if($hasRelations)
                    <flux:callout variant="warning" class="mt-4">
                        <flux:callout.text>
                            {{ __('No se puede eliminar permanentemente esta categoría porque tiene documentos asociados. Primero debes eliminar o reasignar estas relaciones.') }}
                        </flux:callout.text>
                    </flux:callout>
                @endif
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($documentCategoryToForceDelete)
                    @php
                        $documentCategory = \App\Models\DocumentCategory::onlyTrashed()->find($documentCategoryToForceDelete);
                        $hasRelations = $documentCategory && $documentCategory->documents()->exists();
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
    <x-ui.toast event="document-category-deleted" variant="success" />
    <x-ui.toast event="document-category-restored" variant="success" />
    <x-ui.toast event="document-category-force-deleted" variant="warning" />
    <x-ui.toast event="document-category-force-delete-error" variant="error" />
    <x-ui.toast event="document-category-delete-error" variant="error" />
</div>
