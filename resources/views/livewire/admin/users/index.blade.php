<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Usuarios') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona los usuarios del sistema y sus roles') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.users.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Usuario') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('common.nav.users'), 'icon' => 'user-group'],
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
                        :placeholder="__('Buscar por nombre o email...')"
                    />
                </div>

                {{-- Role Filter --}}
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Rol') }}</flux:label>
                        <select wire:model.live="filterRole" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos los roles') }}</option>
                            @foreach($this->roles as $role)
                                <option value="{{ $role->name }}" @selected($filterRole === $role->name)>
                                    {{ $this->getRoleDisplayName($role->name) }}
                                </option>
                            @endforeach
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
    <div wire:loading.delay wire:target="search,sortBy,updatedFilterRole,updatedShowDeleted" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedFilterRole,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->users->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay usuarios')"
                :description="__('No se encontraron usuarios que coincidan con los filtros aplicados.')"
                icon="user-group"
                :action="__('Crear Usuario')"
                :actionHref="route('admin.users.create')"
                actionIcon="plus"
            />
        @else
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Usuario') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('email')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Email') }}
                                        @if($sortField === 'email')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Roles') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Actividad') }}
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
                            @foreach($this->users as $user)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <x-ui.user-avatar :user="$user" size="sm" />
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                        {{ $user->name }}
                                                    </div>
                                                    @if($user->trashed())
                                                        <x-ui.badge variant="danger" size="sm">
                                                            {{ __('Eliminado') }}
                                                        </x-ui.badge>
                                                    @endif
                                                    @if($user->id === auth()->id())
                                                        <x-ui.badge variant="info" size="sm">
                                                            {{ __('Tú') }}
                                                        </x-ui.badge>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-ui.user-roles :user="$user" size="sm" :show-empty="false" />
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        @php
                                            $activitiesCount = \Spatie\Activitylog\Models\Activity::where('causer_type', \App\Models\User::class)
                                                ->where('causer_id', $user->id)
                                                ->count();
                                        @endphp
                                        @if($activitiesCount > 0)
                                            <flux:tooltip content="{{ __('Número de acciones registradas en el sistema') }}" position="top">
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($activitiesCount, 0, ',', '.') }}</span>
                                            </flux:tooltip>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.users.show', $user) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles del usuario')"
                                            />

                                            @if(!$user->trashed())
                                                {{-- Edit --}}
                                                @can('update', $user)
                                                    <flux:button 
                                                        href="{{ route('admin.users.edit', $user) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar usuario')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @php
                                                    $canDelete = $this->canDeleteUser($user);
                                                    $deleteTooltip = !$canDelete 
                                                        ? __('No puedes eliminarte a ti mismo.') 
                                                        : __('Eliminar usuario');
                                                @endphp
                                                @can('delete', $user)
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $user->id }})" 
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
                                                @can('restore', $user)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $user->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('Restaurar')"
                                                        :tooltip="__('Restaurar usuario eliminado')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $user)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $user->id }})" 
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
                    {{ $this->users->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-user" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Usuario') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar este usuario?') }}
                @if($userToDelete)
                    @php
                        $user = \App\Models\User::find($userToDelete);
                    @endphp
                    @if($user)
                        <br>
                        <strong>{{ $user->name }}</strong> ({{ $user->email }})
                        @if($user->id === auth()->id())
                            <br><br>
                            <span class="text-red-600 dark:text-red-400 font-medium">
                                {{ __('No puedes eliminarte a ti mismo.') }}
                            </span>
                        @endif
                    @endif
                @endif
            </flux:text>
            @if($userToDelete)
                @php
                    $user = \App\Models\User::find($userToDelete);
                    $canDelete = $user && $user->id !== auth()->id();
                @endphp
                @if($canDelete)
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Esta acción marcará el usuario como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.') }}
                    </flux:text>
                @endif
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará el usuario como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($userToDelete)
                    @php
                        $user = \App\Models\User::find($userToDelete);
                        $canDelete = $user && $user->id !== auth()->id();
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
    <flux:modal name="restore-user" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Usuario') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar este usuario?') }}
                @if($userToRestore)
                    @php
                        $user = \App\Models\User::onlyTrashed()->find($userToRestore);
                    @endphp
                    @if($user)
                        <br>
                        <strong>{{ $user->name }}</strong> ({{ $user->email }})
                    @endif
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
    <flux:modal name="force-delete-user" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente este usuario?') }}
                @if($userToForceDelete)
                    @php
                        $user = \App\Models\User::onlyTrashed()->find($userToForceDelete);
                    @endphp
                    @if($user)
                        <br>
                        <strong>{{ $user->name }}</strong> ({{ $user->email }})
                        @if($user->id === auth()->id())
                            <br><br>
                            <span class="text-red-600 dark:text-red-400 font-medium">
                                {{ __('No puedes eliminarte a ti mismo.') }}
                            </span>
                        @endif
                    @endif
                @endif
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). El usuario se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($userToForceDelete)
                    @php
                        $user = \App\Models\User::onlyTrashed()->find($userToForceDelete);
                        $canDelete = $user && $user->id !== auth()->id();
                    @endphp
                    <flux:button 
                        type="submit" 
                        variant="danger"
                        :disabled="!$canDelete"
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
    <x-ui.toast event="user-deleted" variant="success" />
    <x-ui.toast event="user-restored" variant="success" />
    <x-ui.toast event="user-force-deleted" variant="warning" />
    <x-ui.toast event="user-delete-error" variant="error" />
    <x-ui.toast event="user-force-delete-error" variant="error" />
</div>
