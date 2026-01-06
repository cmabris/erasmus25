<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-ui.user-avatar :user="$user" size="lg" />
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ $user->name }}
                        </h1>
                        @if($user->trashed())
                            <x-ui.badge variant="danger" size="lg">
                                {{ __('Eliminado') }}
                            </x-ui.badge>
                        @endif
                        @if($user->id === auth()->id())
                            <x-ui.badge variant="info" size="lg">
                                {{ __('Tú') }}
                            </x-ui.badge>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $user->email }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if(!$user->trashed())
                    @if($this->canEdit())
                        <flux:button 
                            href="{{ route('admin.users.edit', $user) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endif
                @endif
                <flux:button 
                    href="{{ route('admin.users.index') }}" 
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
                ['label' => __('common.nav.users'), 'href' => route('admin.users.index'), 'icon' => 'user-group'],
                ['label' => $user->name, 'icon' => 'user'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal Information --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Información Personal') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Nombre') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Correo Electrónico') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Email Verificado') }}</p>
                            <div class="mt-1">
                                @if($user->email_verified_at)
                                    <x-ui.badge variant="success" size="sm">
                                        {{ __('Sí') }} - {{ $user->email_verified_at->format('d/m/Y H:i') }}
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning" size="sm">
                                        {{ __('No') }}
                                    </x-ui.badge>
                                @endif
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('2FA Habilitado') }}</p>
                            <div class="mt-1">
                                @if($user->two_factor_secret)
                                    <x-ui.badge variant="success" size="sm">
                                        {{ __('Sí') }}
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral" size="sm">
                                        {{ __('No') }}
                                    </x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Roles and Permissions --}}
            <x-ui.card>
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="md">{{ __('Roles y Permisos') }}</flux:heading>
                        @if($this->canAssignRoles())
                            <flux:button 
                                wire:click="openAssignRolesModal" 
                                variant="ghost" 
                                size="sm"
                                icon="pencil"
                            >
                                {{ __('Editar Roles') }}
                            </flux:button>
                        @endif
                    </div>

                    {{-- Roles --}}
                    <div class="mb-4">
                        <p class="mb-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Roles Asignados') }}</p>
                        <x-ui.user-roles :user="$user" size="sm" :show-empty="true" />
                    </div>

                    {{-- Direct Permissions --}}
                    <div>
                        <p class="mb-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Permisos Directos') }}</p>
                        <x-ui.user-permissions :user="$user" size="sm" :show-empty="true" />
                    </div>

                    @if($user->id === auth()->id())
                        <flux:callout variant="info" class="mt-4">
                            <flux:callout.text>
                                {{ __('No puedes modificar tus propios roles. Contacta con un administrador si necesitas cambiar tus permisos.') }}
                            </flux:callout.text>
                        </flux:callout>
                    @endif
                </div>
            </x-ui.card>

            {{-- Statistics --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Estadísticas') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                    <flux:icon name="chart-bar" class="[:where(&)]:size-5 text-blue-600 dark:text-blue-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Total de Acciones') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ number_format($this->statistics['total_actions'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @if($this->statistics['last_activity'])
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                                        <flux:icon name="clock" class="[:where(&)]:size-5 text-green-600 dark:text-green-400" variant="outline" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Última Actividad') }}</p>
                                        <p class="text-sm font-bold text-zinc-900 dark:text-white">
                                            {{ $this->statistics['last_activity']->diffForHumans() }}
                                        </p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $this->statistics['last_activity']->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Actions by Type --}}
                    @if(!empty($this->statistics['actions_by_type']))
                        <div class="mt-4">
                            <p class="mb-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Acciones por Tipo') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->statistics['actions_by_type'] as $action => $count)
                                    <x-ui.badge :variant="$this->getActionBadgeVariant($action)" size="sm">
                                        {{ $this->getActionDisplayName($action) }}: {{ $count }}
                                    </x-ui.badge>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Recent Activity (Audit Logs) --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Actividad Reciente') }}</flux:heading>
                    @if($this->auditLogs->isEmpty())
                        <div class="text-center py-8">
                            <flux:icon name="document-text" class="[:where(&)]:size-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-4" variant="outline" />
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('No hay actividad registrada para este usuario') }}
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($this->auditLogs as $log)
                                <x-ui.audit-log-entry :log="$log" />
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($this->auditLogs->hasPages())
                            <div class="mt-6">
                                {{ $this->auditLogs->links() }}
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
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($user->trashed())
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Eliminado') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $user->deleted_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Total de Acciones') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ number_format($this->statistics['total_actions'], 0, ',', '.') }}</p>
                        </div>
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
                        @if(!$user->trashed())
                            @if($this->canEdit())
                                <flux:button 
                                    href="{{ route('admin.users.edit', $user) }}" 
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
                                    wire:click="$set('showDeleteModal', true)" 
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                >
                                    {{ __('common.actions.delete') }}
                                </flux:button>
                            @endif
                        @else
                            @can('restore', $user)
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)" 
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('Restaurar') }}
                                </flux:button>
                            @endcan

                            @can('forceDelete', $user)
                                <flux:button 
                                    wire:click="$set('showForceDeleteModal', true)" 
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                >
                                    {{ __('Eliminar permanentemente') }}
                                </flux:button>
                            @endcan
                        @endif

                        <flux:button 
                            href="{{ route('admin.users.index') }}" 
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
    <flux:modal name="delete-user" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Usuario') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar este usuario?') }}
                <br>
                <strong>{{ $user->name }}</strong> ({{ $user->email }})
            </flux:text>
            @if($user->id === auth()->id())
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No puedes eliminarte a ti mismo.') }}
                    </flux:callout.text>
                </flux:callout>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará el usuario como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($user->id !== auth()->id())
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
                <br>
                <strong>{{ $user->name }}</strong> ({{ $user->email }})
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
                <br>
                <strong>{{ $user->name }}</strong> ({{ $user->email }})
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). El usuario se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($user->id === auth()->id())
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No puedes eliminarte a ti mismo.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($user->id !== auth()->id())
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

    {{-- Assign Roles Modal --}}
    <flux:modal name="assign-roles" wire:model.self="showAssignRolesModal">
        <form wire:submit="assignRoles" class="space-y-4">
            <flux:heading>{{ __('Asignar Roles') }}</flux:heading>
            <flux:text>
                {{ __('Selecciona los roles para el usuario') }}: <strong>{{ $user->name }}</strong>
            </flux:text>

            @if($this->roles->isNotEmpty())
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($this->roles as $role)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 p-4 transition-colors hover:border-erasmus-300 hover:bg-erasmus-50 dark:border-zinc-700 dark:hover:border-erasmus-600 dark:hover:bg-erasmus-900/10">
                            <input 
                                type="checkbox"
                                wire:model.live="selectedRoles"
                                value="{{ $role->name }}"
                                class="mt-0.5 size-4 rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                            />
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ $this->getRoleDisplayName($role->name) }}
                                    </div>
                                    <x-ui.badge :variant="$this->getRoleBadgeVariant($role->name)" size="sm">
                                        {{ $role->name }}
                                    </x-ui.badge>
                                </div>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $this->getRoleDescription($role->name) }}
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <flux:callout variant="warning">
                    <flux:callout.text>
                        {{ __('No hay roles disponibles en el sistema.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif

            @error('roles')
                <flux:error>{{ $message }}</flux:error>
            @enderror
            @error('roles.*')
                <flux:error>{{ $message }}</flux:error>
            @enderror

            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showAssignRolesModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="primary"
                    wire:loading.attr="disabled"
                    wire:target="assignRoles"
                >
                    <span wire:loading.remove wire:target="assignRoles">
                        {{ __('Guardar Roles') }}
                    </span>
                    <span wire:loading wire:target="assignRoles">
                        {{ __('Guardando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="user-deleted" variant="success" />
    <x-ui.toast event="user-restored" variant="success" />
    <x-ui.toast event="user-force-deleted" variant="warning" />
    <x-ui.toast event="user-delete-error" variant="error" />
    <x-ui.toast event="user-force-delete-error" variant="error" />
    <x-ui.toast event="user-roles-updated" variant="success" />
</div>
