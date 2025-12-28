<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Programas Erasmus+') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona los programas Erasmus+ disponibles en el sistema') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.programs.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Programa') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Programas'), 'icon' => 'academic-cap'],
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
                        :placeholder="__('Buscar por código, nombre o descripción...')"
                    />
                </div>

                {{-- Active Filter --}}
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Estado') }}</flux:label>
                        <select wire:model.live="showActiveOnly" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            <option value="1" @selected($showActiveOnly === '1')>{{ __('Activos') }}</option>
                            <option value="0" @selected($showActiveOnly === '0')>{{ __('Inactivos') }}</option>
                        </select>
                    </flux:field>
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
    <div wire:loading.delay wire:target="search,sortBy,updatedShowActiveOnly,updatedShowTrashed" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Programs Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedShowActiveOnly,updatedShowTrashed" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->programs->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay programas')"
                :description="__('No se encontraron programas que coincidan con los filtros aplicados.')"
                icon="academic-cap"
                :action="__('Crear Programa')"
                :actionHref="route('admin.programs.create')"
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
                                        wire:click="sortBy('code')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Código') }}
                                        @if($sortField === 'code')
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
                                    {{ __('Estado') }}
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
                            @foreach($this->programs as $program)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-white min-w-[2rem]">{{ $program->order }}</span>
                                            @if(!$program->trashed())
                                                @can('update', $program)
                                                    <div class="flex flex-col gap-0.5">
                                                        <button 
                                                            wire:click="moveUp({{ $program->id }})" 
                                                            wire:loading.attr="disabled"
                                                            wire:target="moveUp({{ $program->id }})"
                                                            @if(!$this->canMoveUp($program->id)) disabled @endif
                                                            class="flex items-center justify-center p-0.5 rounded text-zinc-600 hover:text-erasmus-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-erasmus-400 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:text-zinc-600 dark:disabled:hover:text-zinc-400 transition-colors"
                                                            title="{{ __('Mover arriba') }}"
                                                        >
                                                            <flux:icon name="chevron-up" class="[:where(&)]:size-3" variant="outline" />
                                                        </button>
                                                        <button 
                                                            wire:click="moveDown({{ $program->id }})" 
                                                            wire:loading.attr="disabled"
                                                            wire:target="moveDown({{ $program->id }})"
                                                            @if(!$this->canMoveDown($program->id)) disabled @endif
                                                            class="flex items-center justify-center p-0.5 rounded text-zinc-600 hover:text-erasmus-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-erasmus-400 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:text-zinc-600 dark:disabled:hover:text-zinc-400 transition-colors"
                                                            title="{{ __('Mover abajo') }}"
                                                        >
                                                            <flux:icon name="chevron-down" class="[:where(&)]:size-3" variant="outline" />
                                                        </button>
                                                    </div>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $program->code }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $program->name }}
                                            </div>
                                            @if($program->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                        @if($program->description)
                                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                {{ \Illuminate\Support\Str::limit($program->description, 60) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($program->is_active)
                                            <x-ui.badge variant="success" size="sm">
                                                {{ __('common.status.active') }}
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral" size="sm">
                                                {{ __('common.status.inactive') }}
                                            </x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col gap-1">
                                            <span>{{ $program->calls_count }} {{ __('Convocatorias') }}</span>
                                            <span>{{ $program->news_posts_count }} {{ __('Noticias') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.programs.show', $program) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('common.actions.view_program_details')"
                                            />

                                            @if(!$program->trashed())
                                                {{-- Edit --}}
                                                @can('update', $program)
                                                    <flux:button 
                                                        href="{{ route('admin.programs.edit', $program) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('common.actions.edit_program')"
                                                    />
                                                @endcan

                                                {{-- Toggle Active --}}
                                                @can('update', $program)
                                                    <flux:button 
                                                        wire:click="toggleActive({{ $program->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        :icon="$program->is_active ? 'power' : 'bolt'"
                                                        :label="$program->is_active ? __('Desactivar') : __('Activar')"
                                                        :tooltip="$program->is_active ? __('common.actions.deactivate_program') : __('common.actions.activate_program')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @can('delete', $program)
                                                    @php
                                                        $hasRelations = $program->calls_count > 0 || $program->news_posts_count > 0;
                                                        $canDelete = $this->canDeleteProgram($program);
                                                        $deleteTooltip = !$canDelete 
                                                            ? __('common.errors.cannot_delete_with_relations') 
                                                            : __('common.actions.delete_program');
                                                    @endphp
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $program->id }})" 
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
                                                @can('restore', $program)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $program->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('Restaurar')"
                                                        :tooltip="__('common.actions.restore_program')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $program)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $program->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('Eliminar permanentemente')"
                                                        :tooltip="__('common.actions.permanently_delete_program')"
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
                    {{ $this->programs->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-program" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Programa') }}</flux:heading>
            <flux:text>
                {{ __('common.messages.confirm_delete_program') }}
                @if($programToDelete)
                    @php
                        $program = \App\Models\Program::find($programToDelete);
                        $hasRelations = $program && ($program->calls()->exists() || $program->newsPosts()->exists());
                    @endphp
                    <br>
                    <strong>{{ $program?->name }}</strong>
                    @if($hasRelations)
                        <br><br>
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            {{ __('No se puede eliminar este programa porque tiene relaciones activas (convocatorias o noticias).') }}
                        </span>
                    @endif
                @endif
            </flux:text>
            @if($programToDelete)
                @php
                    $program = \App\Models\Program::find($programToDelete);
                    $hasRelations = $program && ($program->calls()->exists() || $program->newsPosts()->exists());
                @endphp
                @if(!$hasRelations)
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('common.messages.soft_delete_explanation') }}
                    </flux:text>
                @endif
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('common.messages.soft_delete_explanation') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($programToDelete)
                    @php
                        $program = \App\Models\Program::find($programToDelete);
                        $canDelete = $program && !($program->calls()->exists() || $program->newsPosts()->exists());
                    @endphp
                    @if($canDelete)
                        <flux:button type="submit" variant="danger">
                            {{ __('common.actions.delete') }}
                        </flux:button>
                    @endif
                @else
                    <flux:button type="submit" variant="danger">
                        {{ __('common.actions.delete') }}
                    </flux:button>
                @endif
            </div>
        </form>
    </flux:modal>

    {{-- Restore Confirmation Modal --}}
    <flux:modal name="restore-program" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Programa') }}</flux:heading>
            <flux:text>
                {{ __('common.messages.confirm_restore_program') }}
                @if($programToRestore)
                    <br>
                    <strong>{{ \App\Models\Program::onlyTrashed()->find($programToRestore)?->name }}</strong>
                @endif
            </flux:text>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showRestoreModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Restaurar') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Confirmation Modal --}}
    <flux:modal name="force-delete-program" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('common.messages.confirm_force_delete_program') }}
                @if($programToForceDelete)
                    <br>
                    <strong>{{ \App\Models\Program::onlyTrashed()->find($programToForceDelete)?->name }}</strong>
                @endif
            </flux:text>
            <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ __('common.messages.action_cannot_be_undone') }}
            </flux:text>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button type="submit" variant="danger">
                    {{ __('Eliminar permanentemente') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="program-updated" variant="success" />
    <x-ui.toast event="program-deleted" variant="success" />
    <x-ui.toast event="program-restored" variant="success" />
    <x-ui.toast event="program-force-deleted" variant="warning" />
    <x-ui.toast event="program-force-delete-error" variant="error" />
    <x-ui.toast event="program-delete-error" variant="error" />
</div>
