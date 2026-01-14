<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $subscription->email }}
                    </h1>
                    <x-ui.badge :variant="$this->getStatusBadge()" size="lg">
                        {{ $subscription->is_active ? __('Activo') : __('Inactivo') }}
                    </x-ui.badge>
                    <x-ui.badge :variant="$this->getVerificationBadge()" size="lg">
                        {{ $subscription->isVerified() ? __('Verificado') : __('No Verificado') }}
                    </x-ui.badge>
                </div>
                @if($subscription->name)
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $subscription->name }}
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($this->canDelete())
                    <flux:button 
                        wire:click="$set('showDeleteModal', true)" 
                        variant="danger"
                        icon="trash"
                    >
                        {{ __('common.actions.delete') }}
                    </flux:button>
                @endif
                <flux:button 
                    href="{{ route('admin.newsletter.index') }}" 
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
                ['label' => __('Suscripciones Newsletter'), 'href' => route('admin.newsletter.index'), 'icon' => 'envelope'],
                ['label' => $subscription->email, 'icon' => 'envelope'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic Information --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Información Básica') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Correo Electrónico') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $subscription->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Nombre') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $subscription->name ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Estado') }}</p>
                            <div class="mt-1">
                                <x-ui.badge :variant="$this->getStatusBadge()" size="sm">
                                    {{ $subscription->is_active ? __('Activo') : __('Inactivo') }}
                                </x-ui.badge>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Verificación') }}</p>
                            <div class="mt-1">
                                <x-ui.badge :variant="$this->getVerificationBadge()" size="sm">
                                    {{ $subscription->isVerified() ? __('Verificado') : __('No Verificado') }}
                                </x-ui.badge>
                                @if($subscription->verified_at)
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('Verificado el') }} {{ $subscription->verified_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Fecha de Suscripción') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                                {{ $subscription->subscribed_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        @if($subscription->unsubscribed_at)
                            <div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Fecha de Baja') }}</p>
                                <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                                    {{ $subscription->unsubscribed_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Programs of Interest --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Programas de Interés') }}</flux:heading>
                    @if($subscription->programs && count($subscription->programs) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($this->programModels as $program)
                                <x-ui.badge variant="primary" size="md">
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="academic-cap" class="[:where(&)]:size-4" variant="outline" />
                                        <span>{{ $program->name }}</span>
                                    </div>
                                </x-ui.badge>
                            @endforeach
                            @php
                                $programCodes = $subscription->programs;
                                $foundCodes = $this->programModels->pluck('code')->toArray();
                                $notFoundCodes = array_diff($programCodes, $foundCodes);
                            @endphp
                            @if(count($notFoundCodes) > 0)
                                @foreach($notFoundCodes as $code)
                                    <x-ui.badge variant="neutral" size="md">
                                        <div class="flex items-center gap-2">
                                            <flux:icon name="academic-cap" class="[:where(&)]:size-4" variant="outline" />
                                            <span>{{ $code }}</span>
                                        </div>
                                    </x-ui.badge>
                                @endforeach
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8">
                            <flux:icon name="academic-cap" class="[:where(&)]:size-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-4" variant="outline" />
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('No hay programas de interés seleccionados') }}
                            </p>
                        </div>
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
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Email') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white break-all">{{ $subscription->email }}</p>
                        </div>
                        @if($subscription->name)
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Nombre') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $subscription->name }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Estado') }}</p>
                            <div class="mt-1">
                                <x-ui.badge :variant="$this->getStatusBadge()" size="sm">
                                    {{ $subscription->is_active ? __('Activo') : __('Inactivo') }}
                                </x-ui.badge>
                            </div>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Verificado') }}</p>
                            <div class="mt-1">
                                <x-ui.badge :variant="$this->getVerificationBadge()" size="sm">
                                    {{ $subscription->isVerified() ? __('Sí') : __('No') }}
                                </x-ui.badge>
                            </div>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Suscripción') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $subscription->subscribed_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($subscription->verified_at)
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Verificado el') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $subscription->verified_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                        @if($subscription->unsubscribed_at)
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Baja el') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $subscription->unsubscribed_at->format('d/m/Y H:i') }}</p>
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

                        <flux:button 
                            href="{{ route('admin.newsletter.index') }}" 
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
    <flux:modal name="delete-newsletter-subscription" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Suscripción') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta suscripción?') }}
                <br>
                <strong>{{ $subscription->email }}</strong>
            </flux:text>
            <flux:callout variant="warning" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Permanente') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción eliminará permanentemente la suscripción del sistema. Esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
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
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="newsletter-subscription-deleted" variant="success" />
</div>
