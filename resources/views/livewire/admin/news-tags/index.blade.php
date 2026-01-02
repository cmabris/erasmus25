<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Etiquetas de Noticias') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona las etiquetas disponibles para las noticias') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.news-tags.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Etiqueta') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Etiquetas de Noticias'), 'icon' => 'tag'],
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
                        :placeholder="__('Buscar por nombre o slug...')"
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

    {{-- News Tags Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->newsTags->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay etiquetas')"
                :description="__('No se encontraron etiquetas que coincidan con los filtros aplicados.')"
                icon="tag"
                :action="__('Crear Etiqueta')"
                :actionHref="route('admin.news-tags.create')"
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
                                    {{ __('Noticias') }}
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
                            @foreach($this->newsTags as $newsTag)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $newsTag->name }}
                                            </div>
                                            @if($newsTag->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $newsTag->slug }}</code>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($newsTag->news_posts_count > 0)
                                            <flux:tooltip content="{{ __('Número de noticias asociadas a esta etiqueta') }}" position="top">
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $newsTag->news_posts_count }}</span>
                                            </flux:tooltip>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $newsTag->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.news-tags.show', $newsTag) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles de la etiqueta')"
                                            />

                                            @if(!$newsTag->trashed())
                                                {{-- Edit --}}
                                                @can('update', $newsTag)
                                                    <flux:button 
                                                        href="{{ route('admin.news-tags.edit', $newsTag) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar etiqueta')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @can('delete', $newsTag)
                                                    @php
                                                        $hasRelations = $newsTag->news_posts_count > 0;
                                                        $canDelete = $this->canDeleteNewsTag($newsTag);
                                                        $deleteTooltip = !$canDelete 
                                                            ? __('No se puede eliminar la etiqueta porque tiene noticias asociadas.') 
                                                            : __('Eliminar etiqueta');
                                                    @endphp
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $newsTag->id }})" 
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
                                                @can('restore', $newsTag)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $newsTag->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('Restaurar')"
                                                        :tooltip="__('Restaurar etiqueta eliminada')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $newsTag)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $newsTag->id }})" 
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
                    {{ $this->newsTags->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-news-tag" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Etiqueta') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta etiqueta?') }}
                @if($newsTagToDelete)
                    @php
                        $newsTag = \App\Models\NewsTag::find($newsTagToDelete);
                        $hasRelations = $newsTag && $newsTag->newsPosts()->exists();
                    @endphp
                    <br>
                    <strong>{{ $newsTag?->name }}</strong>
                    @if($hasRelations)
                        <br><br>
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            {{ __('No se puede eliminar esta etiqueta porque tiene noticias asociadas.') }}
                        </span>
                    @endif
                @endif
            </flux:text>
            @if($newsTagToDelete)
                @php
                    $newsTag = \App\Models\NewsTag::find($newsTagToDelete);
                    $hasRelations = $newsTag && $newsTag->newsPosts()->exists();
                @endphp
                @if(!$hasRelations)
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Esta acción marcará la etiqueta como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
                    </flux:text>
                @endif
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará la etiqueta como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($newsTagToDelete)
                    @php
                        $newsTag = \App\Models\NewsTag::find($newsTagToDelete);
                        $canDelete = $newsTag && !$newsTag->newsPosts()->exists();
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
    <flux:modal name="restore-news-tag" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Etiqueta') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar esta etiqueta?') }}
                @if($newsTagToRestore)
                    <br>
                    <strong>{{ \App\Models\NewsTag::onlyTrashed()->find($newsTagToRestore)?->name }}</strong>
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
    <flux:modal name="force-delete-news-tag" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente esta etiqueta?') }}
                @if($newsTagToForceDelete)
                    <br>
                    <strong>{{ \App\Models\NewsTag::onlyTrashed()->find($newsTagToForceDelete)?->name }}</strong>
                @endif
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). La etiqueta se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($newsTagToForceDelete)
                @php
                    $newsTag = \App\Models\NewsTag::onlyTrashed()->find($newsTagToForceDelete);
                    $hasRelations = $newsTag && $newsTag->newsPosts()->exists();
                @endphp
                @if($hasRelations)
                    <flux:callout variant="warning" class="mt-4">
                        <flux:callout.text>
                            {{ __('No se puede eliminar permanentemente esta etiqueta porque tiene noticias asociadas. Primero debes eliminar o reasignar estas relaciones.') }}
                        </flux:callout.text>
                    </flux:callout>
                @endif
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($newsTagToForceDelete)
                    @php
                        $newsTag = \App\Models\NewsTag::onlyTrashed()->find($newsTagToForceDelete);
                        $hasRelations = $newsTag && $newsTag->newsPosts()->exists();
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
    <x-ui.toast event="news-tag-deleted" variant="success" />
    <x-ui.toast event="news-tag-restored" variant="success" />
    <x-ui.toast event="news-tag-force-deleted" variant="warning" />
    <x-ui.toast event="news-tag-force-delete-error" variant="error" />
    <x-ui.toast event="news-tag-delete-error" variant="error" />
</div>
