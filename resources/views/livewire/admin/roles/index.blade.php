<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Roles y Permisos') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona los roles y permisos del sistema') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.roles.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Rol') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Roles y Permisos'), 'icon' => 'shield-check'],
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
                        :placeholder="__('Buscar por nombre...')"
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
        </x-ui.card>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay wire:target="search,sortBy" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Roles Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->roles->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay roles')"
                :description="__('No se encontraron roles que coincidan con los filtros aplicados.')"
                icon="shield-check"
                :action="__('Crear Rol')"
                :actionHref="route('admin.roles.create')"
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
                                        wire:click="sortBy('users_count')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Usuarios') }}
                                        @if($sortField === 'users_count')
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
                                        wire:click="sortBy('permissions_count')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Permisos') }}
                                        @if($sortField === 'permissions_count')
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
                            @foreach($this->roles as $role)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <x-ui.badge 
                                                :variant="$this->getRoleBadgeVariant($role->name)" 
                                                size="sm"
                                            >
                                                {{ $this->getRoleDisplayName($role->name) }}
                                            </x-ui.badge>
                                            @if($this->isSystemRole($role))
                                                <x-ui.badge variant="warning" size="sm" icon="shield-check">
                                                    {{ __('Rol del Sistema') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($role->users_count > 0)
                                            <flux:tooltip content="{{ __('Número de usuarios con este rol') }}" position="top">
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $role->users_count }}</span>
                                            </flux:tooltip>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($role->permissions_count > 0)
                                            <flux:tooltip content="{{ __('Número de permisos asignados a este rol') }}" position="top">
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $role->permissions_count }}</span>
                                            </flux:tooltip>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $role->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.roles.show', $role) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles del rol')"
                                            />

                                            {{-- Edit --}}
                                            @can('update', $role)
                                                <flux:button 
                                                    href="{{ route('admin.roles.edit', $role) }}" 
                                                    variant="ghost" 
                                                    size="sm"
                                                    wire:navigate
                                                    icon="pencil"
                                                    :label="__('common.actions.edit')"
                                                    :tooltip="__('Editar rol')"
                                                />
                                            @endcan

                                            {{-- Delete --}}
                                            @can('delete', $role)
                                                @php
                                                    $canDelete = $this->canDeleteRole($role);
                                                    $deleteTooltip = !$canDelete 
                                                        ? ($this->isSystemRole($role) 
                                                            ? __('No se puede eliminar un rol del sistema.') 
                                                            : __('No se puede eliminar el rol porque tiene usuarios asignados.')) 
                                                        : __('Eliminar rol');
                                                @endphp
                                                <flux:button 
                                                    wire:click="confirmDelete({{ $role->id }})" 
                                                    variant="ghost" 
                                                    size="sm"
                                                    icon="trash"
                                                    :label="__('common.actions.delete')"
                                                    :disabled="!$canDelete"
                                                    :tooltip="$deleteTooltip"
                                                />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $this->roles->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-role" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Rol') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar este rol?') }}
                @if($roleToDelete)
                    @php
                        $role = \Spatie\Permission\Models\Role::withCount(['users'])->find($roleToDelete);
                        $isSystemRole = $role && in_array($role->name, \App\Support\Roles::all(), true);
                        $hasUsers = $role && $role->users_count > 0;
                    @endphp
                    <br>
                    <strong>{{ $role ? $this->getRoleDisplayName($role->name) : '' }}</strong>
                    @if($isSystemRole)
                        <br><br>
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            {{ __('No se puede eliminar un rol del sistema.') }}
                        </span>
                    @elseif($hasUsers)
                        <br><br>
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            {{ __('No se puede eliminar este rol porque tiene usuarios asignados.') }}
                        </span>
                    @endif
                @endif
            </flux:text>
            @if($roleToDelete)
                @php
                    $role = \Spatie\Permission\Models\Role::withCount(['users'])->find($roleToDelete);
                    $isSystemRole = $role && in_array($role->name, \App\Support\Roles::all(), true);
                    $hasUsers = $role && $role->users_count > 0;
                    $canDelete = $role && !$isSystemRole && !$hasUsers;
                @endphp
                @if($canDelete)
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Esta acción eliminará el rol permanentemente del sistema. Esta acción NO se puede deshacer.') }}
                    </flux:text>
                @endif
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción eliminará el rol permanentemente del sistema. Esta acción NO se puede deshacer.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($roleToDelete)
                    @php
                        $role = \Spatie\Permission\Models\Role::withCount(['users'])->find($roleToDelete);
                        $isSystemRole = $role && in_array($role->name, \App\Support\Roles::all(), true);
                        $hasUsers = $role && $role->users_count > 0;
                        $canDelete = $role && !$isSystemRole && !$hasUsers;
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

    {{-- Toast Notifications --}}
    <x-ui.toast event="role-deleted" variant="success" />
    <x-ui.toast event="role-delete-error" variant="error" />
</div>

