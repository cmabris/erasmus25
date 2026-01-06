<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Usuario') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información del usuario') }}: <strong>{{ $user->name }}</strong>
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.users.index') }}" 
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
                ['label' => __('common.nav.users'), 'href' => route('admin.users.index'), 'icon' => 'user-group'],
                ['label' => $user->name, 'href' => route('admin.users.show', $user), 'icon' => 'user'],
                ['label' => __('common.actions.edit'), 'icon' => 'pencil'],
            ]"
        />
    </div>

    {{-- Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <form wire:submit="update" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Main Form Fields --}}
                <div class="lg:col-span-2 space-y-6">
                    <x-ui.card>
                        <div class="space-y-6">
                            {{-- Name Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Nombre') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('El nombre completo del usuario.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    wire:model.blur="name" 
                                    placeholder="Ej: Juan Pérez"
                                    required
                                    autofocus
                                    maxlength="255"
                                />
                                <flux:description>{{ __('El nombre completo del usuario') }}</flux:description>
                                @error('name')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Email Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Correo Electrónico') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('El correo electrónico del usuario. Debe ser único y válido.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    type="email"
                                    wire:model.blur="email" 
                                    placeholder="usuario@ejemplo.com"
                                    required
                                    maxlength="255"
                                />
                                <flux:description>{{ __('El correo electrónico del usuario (debe ser único)') }}</flux:description>
                                @error('email')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Password Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Nueva Contraseña') }}
                                    <flux:tooltip content="{{ __('Deja en blanco para mantener la contraseña actual.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    type="password"
                                    wire:model.blur="password" 
                                    placeholder="••••••••"
                                />
                                <flux:description>{{ __('Deja en blanco para mantener la contraseña actual. Si introduces una nueva, debe tener al menos 8 caracteres.') }}</flux:description>
                                @error('password')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Password Confirmation Field --}}
                            @if(!empty($password))
                                <flux:field>
                                    <flux:label>
                                        {{ __('Confirmar Nueva Contraseña') }}
                                    </flux:label>
                                    <flux:input 
                                        type="password"
                                        wire:model.blur="password_confirmation" 
                                        placeholder="••••••••"
                                    />
                                    <flux:description>{{ __('Repite la nueva contraseña para confirmar') }}</flux:description>
                                    @error('password_confirmation')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            @endif
                        </div>
                    </x-ui.card>

                    {{-- Roles Selection Card --}}
                    @if($this->canAssignRoles())
                        <x-ui.card>
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="sm">{{ __('Roles') }}</flux:heading>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ __('Selecciona uno o más roles para el usuario. Los roles determinan los permisos del usuario en el sistema.') }}
                                    </p>
                                </div>

                                @if($this->roles->isNotEmpty())
                                    <div class="space-y-3">
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
                            </div>
                        </x-ui.card>
                    @else
                        {{-- Current Roles Display (if cannot assign) --}}
                        <x-ui.card>
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="sm">{{ __('Roles Actuales') }}</flux:heading>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($user->roles as $role)
                                        <x-ui.badge :variant="$this->getRoleBadgeVariant($role->name)" size="sm">
                                            {{ $this->getRoleDisplayName($role->name) }}
                                        </x-ui.badge>
                                    @empty
                                        <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('Sin roles asignados') }}</span>
                                    @endforelse
                                </div>
                                @if($user->id === auth()->id())
                                    <flux:callout variant="info">
                                        <flux:callout.text>
                                            {{ __('No puedes modificar tus propios roles. Contacta con un administrador si necesitas cambiar tus permisos.') }}
                                        </flux:callout.text>
                                    </flux:callout>
                                @endif
                            </div>
                        </x-ui.card>
                    @endif
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
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($user->email_verified_at)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Email verificado') }}:</span>
                                        <x-ui.badge variant="success" size="sm">
                                            {{ __('Sí') }}
                                        </x-ui.badge>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Email verificado') }}:</span>
                                        <x-ui.badge variant="warning" size="sm">
                                            {{ __('No') }}
                                        </x-ui.badge>
                                    </div>
                                @endif
                                @if($user->two_factor_secret)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('2FA habilitado') }}:</span>
                                        <x-ui.badge variant="success" size="sm">
                                            {{ __('Sí') }}
                                        </x-ui.badge>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('2FA habilitado') }}:</span>
                                        <x-ui.badge variant="neutral" size="sm">
                                            {{ __('No') }}
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
                                    href="{{ route('admin.users.index') }}" 
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
    <x-ui.toast event="user-updated" variant="success" />
</div>
