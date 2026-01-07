<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Rol') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica el rol y sus permisos') }}: <strong>{{ $this->getRoleDisplayName($role->name) }}</strong>
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.roles.index') }}" 
                variant="ghost"
                wire:navigate
                icon="arrow-left"
            >
                {{ __('common.actions.back') }}
            </flux:button>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Roles y Permisos'), 'href' => route('admin.roles.index'), 'icon' => 'shield-check'],
                ['label' => $this->getRoleDisplayName($role->name), 'href' => route('admin.roles.show', $role), 'icon' => 'shield-check'],
                ['label' => __('common.actions.edit'), 'icon' => 'pencil'],
            ]"
        />
    </div>

    {{-- System Role Warning --}}
    @if($this->isSystemRole())
        <div class="mb-6 animate-fade-in" style="animation-delay: 0.05s;">
            <flux:callout variant="warning">
                <flux:callout.heading>{{ __('Rol del Sistema') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Este es un rol del sistema. El nombre no puede modificarse, pero puedes cambiar los permisos asignados.') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif

    {{-- Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <form wire:submit="update" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Main Form Fields --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Role Name Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Información del Rol') }}</flux:heading>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('El nombre del rol. Los roles del sistema no pueden cambiar su nombre.') }}
                                </p>
                            </div>

                            {{-- Name Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Nombre del Rol') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('El nombre del rol. Los roles del sistema no pueden cambiar su nombre.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:select 
                                    wire:model.blur="name" 
                                    placeholder="{{ __('Selecciona un rol...') }}"
                                    required
                                    :disabled="$this->isSystemRole()"
                                >
                                    <option value="">{{ __('Selecciona un rol...') }}</option>
                                    @foreach(\App\Support\Roles::all() as $roleName)
                                        <option value="{{ $roleName }}" @selected($name === $roleName)>{{ $roleName }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:description>
                                    @if($this->isSystemRole())
                                        {{ __('El nombre de este rol del sistema no puede modificarse.') }}
                                    @else
                                        {{ __('Selecciona uno de los roles válidos del sistema') }}
                                    @endif
                                </flux:description>
                                @error('name')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Permissions Selection Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Permisos') }}</flux:heading>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('Selecciona los permisos que tendrá este rol. Los permisos están organizados por módulo.') }}
                                </p>
                            </div>

                            @if(!empty($this->availablePermissions))
                                <div class="space-y-6">
                                    @foreach($this->availablePermissions as $module => $modulePermissions)
                                        <div class="space-y-3">
                                            {{-- Module Header --}}
                                            <div class="flex items-center justify-between border-b border-zinc-200 pb-2 dark:border-zinc-700">
                                                <div>
                                                    <h3 class="font-semibold text-zinc-900 dark:text-white">
                                                        {{ $this->getModuleDisplayName($module) }}
                                                    </h3>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ count($modulePermissions) }} {{ __('permisos disponibles') }}
                                                    </p>
                                                </div>
                                                <div class="flex gap-2">
                                                    @if($this->areAllModulePermissionsSelected($module))
                                                        <flux:button 
                                                            type="button"
                                                            wire:click="deselectAllModulePermissions('{{ $module }}')"
                                                            variant="ghost"
                                                            size="sm"
                                                            icon="x-mark"
                                                        >
                                                            {{ __('Deseleccionar todos') }}
                                                        </flux:button>
                                                    @else
                                                        <flux:button 
                                                            type="button"
                                                            wire:click="selectAllModulePermissions('{{ $module }}')"
                                                            variant="ghost"
                                                            size="sm"
                                                            icon="check"
                                                        >
                                                            {{ __('Seleccionar todos') }}
                                                        </flux:button>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Permissions List --}}
                                            <div class="space-y-2 pl-2">
                                                @foreach($modulePermissions as $permission)
                                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 p-3 transition-colors hover:border-erasmus-300 hover:bg-erasmus-50 dark:border-zinc-700 dark:hover:border-erasmus-600 dark:hover:bg-erasmus-900/10">
                                                        <input 
                                                            type="checkbox"
                                                            wire:model.live="permissions"
                                                            value="{{ $permission['name'] }}"
                                                            class="size-4 rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                                                        />
                                                        <div class="flex-1">
                                                            <div class="font-medium text-zinc-900 dark:text-white">
                                                                {{ $this->getPermissionDisplayName($permission['name']) }}
                                                            </div>
                                                            <code class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">
                                                                {{ $permission['name'] }}
                                                            </code>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <flux:callout variant="warning">
                                    <flux:callout.text>
                                        {{ __('No hay permisos disponibles en el sistema.') }}
                                    </flux:callout.text>
                                </flux:callout>
                            @endif

                            @error('permissions')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                            @error('permissions.*')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </div>
                    </x-ui.card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Info Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $role->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $role->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Usuarios con este rol') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $role->users()->count() }}</span>
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

                    {{-- Selected Permissions Summary --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Resumen') }}</flux:heading>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Permisos seleccionados:') }}</span>
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        {{ count($permissions) }}
                                    </span>
                                </div>
                                @if(count($permissions) > 0)
                                    <div class="mt-3 max-h-48 space-y-1 overflow-y-auto">
                                        @foreach($permissions as $permissionName)
                                            <div class="rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800">
                                                {{ $permissionName }}
                                            </div>
                                        @endforeach
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
                                <flux:button 
                                    type="submit" 
                                    variant="primary"
                                    icon="check"
                                    class="w-full"
                                    wire:loading.attr="disabled"
                                    wire:target="update"
                                >
                                    <span wire:loading.remove wire:target="update">
                                        {{ __('common.actions.save') }}
                                    </span>
                                    <span wire:loading wire:target="update">
                                        {{ __('Guardando...') }}
                                    </span>
                                </flux:button>

                                <flux:button 
                                    type="button"
                                    href="{{ route('admin.roles.index') }}" 
                                    variant="ghost"
                                    wire:navigate
                                    class="w-full"
                                >
                                    {{ __('common.actions.cancel') }}
                                </flux:button>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </form>
    </div>

    {{-- Toast Notifications --}}
    <x-ui.toast event="role-updated" variant="success" />
</div>

