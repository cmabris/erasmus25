<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex size-16 items-center justify-center rounded-full bg-erasmus-100 dark:bg-erasmus-900/30">
                    <flux:icon name="shield-check" class="[:where(&)]:size-8 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ $this->getRoleDisplayName($role->name) }}
                        </h1>
                        @if($this->isSystemRole())
                            <x-ui.badge variant="warning" size="lg" icon="shield-check">
                                {{ __('Rol del Sistema') }}
                            </x-ui.badge>
                        @endif
                        <x-ui.badge :variant="$this->getRoleBadgeVariant($role->name)" size="lg">
                            {{ $role->name }}
                        </x-ui.badge>
                    </div>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Rol con :count permisos y :users usuarios', ['count' => $role->permissions->count(), 'users' => $role->users_count]) }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($this->canEdit())
                    <flux:button 
                        href="{{ route('admin.roles.edit', $role) }}" 
                        variant="primary"
                        wire:navigate
                        icon="pencil"
                    >
                        {{ __('common.actions.edit') }}
                    </flux:button>
                @endif
                <flux:button 
                    href="{{ route('admin.roles.index') }}" 
                    variant="ghost"
                    wire:navigate
                    icon="arrow-left"
                >
                    {{ __('common.actions.back') }}
                </flux:button>
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Roles y Permisos'), 'href' => route('admin.roles.index'), 'icon' => 'shield-check'],
                ['label' => $this->getRoleDisplayName($role->name), 'icon' => 'shield-check'],
            ]"
        />
    </div>

    {{-- System Role Warning --}}
    @if($this->isSystemRole())
        <div class="mb-6 animate-fade-in" style="animation-delay: 0.05s;">
            <flux:callout variant="info">
                <flux:callout.heading>{{ __('Rol del Sistema') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Este es un rol del sistema. El nombre no puede modificarse ni eliminarse, pero puedes cambiar los permisos asignados.') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Role Information --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Información del Rol') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Nombre') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $role->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Nombre para Mostrar') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $this->getRoleDisplayName($role->name) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Total de Permisos') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $role->permissions->count() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Total de Usuarios') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $role->users_count }}</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Permissions by Module --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Permisos Asignados') }}</flux:heading>
                    @if($role->permissions->isEmpty())
                        <div class="text-center py-8">
                            <flux:icon name="lock-closed" class="[:where(&)]:size-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-4" variant="outline" />
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('Este rol no tiene permisos asignados') }}
                            </p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($this->permissionsByModule as $module => $permissions)
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 border-b border-zinc-200 pb-2 dark:border-zinc-700">
                                        <h3 class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $this->getModuleDisplayName($module) }}
                                        </h3>
                                        <x-ui.badge variant="neutral" size="sm">
                                            {{ count($permissions) }} {{ __('permisos') }}
                                        </x-ui.badge>
                                    </div>
                                    <div class="flex flex-wrap gap-2 pl-2">
                                        @foreach($permissions as $permissionName)
                                            <x-ui.badge variant="info" size="sm">
                                                {{ $this->getPermissionDisplayName($permissionName) }}
                                                <code class="ml-1 text-xs opacity-75">{{ $permissionName }}</code>
                                            </x-ui.badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Users with this Role --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Usuarios con este Rol') }}</flux:heading>
                    @if($this->users->isEmpty())
                        <div class="text-center py-8">
                            <flux:icon name="user-group" class="[:where(&)]:size-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-4" variant="outline" />
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('No hay usuarios con este rol asignado') }}
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            {{ __('Usuario') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            {{ __('Email') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            {{ __('Roles') }}
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
                                                    <div class="font-medium text-zinc-900 dark:text-white">
                                                        {{ $user->name }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $user->email }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <x-ui.user-roles :user="$user" size="sm" :show-empty="false" />
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <flux:button 
                                                    href="{{ route('admin.users.show', $user) }}" 
                                                    variant="ghost" 
                                                    size="sm"
                                                    wire:navigate
                                                    icon="eye"
                                                    :label="__('common.actions.view')"
                                                    :tooltip="__('Ver detalles del usuario')"
                                                />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($this->users->hasPages())
                            <div class="mt-6">
                                {{ $this->users->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Information Card --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $role->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $role->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Total de Permisos') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $role->permissions->count() }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Total de Usuarios') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $role->users_count }}</p>
                        </div>
                        @if($this->isSystemRole())
                            <div class="mt-3">
                                <x-ui.badge variant="warning" size="sm" icon="shield-check">
                                    {{ __('Rol del Sistema') }}
                                </x-ui.badge>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Actions Card --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Acciones') }}</flux:heading>
                    </div>
                    <div class="flex flex-col gap-2">
                        @if($this->canEdit())
                            <flux:button 
                                href="{{ route('admin.roles.edit', $role) }}" 
                                variant="primary"
                                wire:navigate
                                icon="pencil"
                                class="w-full"
                            >
                                {{ __('common.actions.edit') }}
                            </flux:button>
                        @endif

                        @if($this->canDelete())
                            <flux:button 
                                wire:click="confirmDelete" 
                                variant="danger"
                                icon="trash"
                                class="w-full"
                            >
                                {{ __('common.actions.delete') }}
                            </flux:button>
                        @elseif($this->isSystemRole())
                            <flux:button 
                                variant="ghost"
                                icon="shield-check"
                                class="w-full"
                                disabled
                            >
                                {{ __('No se puede eliminar') }}
                            </flux:button>
                        @elseif($role->users_count > 0)
                            <flux:button 
                                variant="ghost"
                                icon="user-group"
                                class="w-full"
                                disabled
                            >
                                {{ __('Tiene usuarios asignados') }}
                            </flux:button>
                        @endif

                        <flux:button 
                            href="{{ route('admin.roles.index') }}" 
                            variant="ghost"
                            wire:navigate
                            icon="arrow-left"
                            class="w-full"
                        >
                            {{ __('common.actions.back') }}
                        </flux:button>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-role" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Rol') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar este rol?') }}
                <br>
                <strong>{{ $this->getRoleDisplayName($role->name) }}</strong> ({{ $role->name }})
            </flux:text>
            @if($this->isSystemRole())
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar un rol del sistema.') }}
                    </flux:callout.text>
                </flux:callout>
            @elseif($role->users_count > 0)
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar este rol porque tiene :count usuarios asignados.', ['count' => $role->users_count]) }}
                    </flux:callout.text>
                </flux:callout>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción eliminará el rol permanentemente del sistema. Esta acción NO se puede deshacer.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($this->canDelete())
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

