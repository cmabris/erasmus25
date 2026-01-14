<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Suscripciones Newsletter') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona todas las suscripciones al newsletter del sistema') }}
                </p>
            </div>
            @if($this->canExport())
                <flux:button 
                    wire:click="export"
                    variant="primary"
                    icon="arrow-down-tray"
                >
                    {{ __('Exportar') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Suscripciones Newsletter'), 'icon' => 'envelope'],
            ]"
        />
    </div>

    {{-- Statistics --}}
    <div class="mb-6 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-ui.stat-card
                :value="$this->statistics['total']"
                :label="__('Total Suscripciones')"
                icon="envelope"
                color="primary"
                variant="default"
            />
            <x-ui.stat-card
                :value="$this->statistics['active']"
                :label="__('Suscripciones Activas')"
                icon="check-circle"
                color="success"
                variant="default"
            />
            <x-ui.stat-card
                :value="$this->statistics['verified']"
                :label="__('Suscripciones Verificadas')"
                icon="shield-check"
                color="info"
                variant="default"
            />
        </div>
    </div>

    {{-- Filters and Search --}}
    <div class="mb-6 animate-fade-in" style="animation-delay: 0.2s;">
        <x-ui.card>
            <div class="flex flex-col gap-4">
                {{-- First Row: Search and Reset --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    {{-- Search --}}
                    <div class="flex-1">
                        <x-ui.search-input 
                            wire:model.live.debounce.300ms="search"
                            :placeholder="__('Buscar por email o nombre...')"
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

                {{-- Second Row: Filters --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {{-- Program Filter --}}
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Programa') }}</flux:label>
                            <select wire:model.live="filterProgram" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todos los programas') }}</option>
                                @foreach($this->programs as $program)
                                    <option value="{{ $program->code }}" @selected($filterProgram === $program->code)>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </flux:field>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Estado') }}</flux:label>
                            <select wire:model.live="filterStatus" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todos los estados') }}</option>
                                <option value="activo" @selected($filterStatus === 'activo')>{{ __('Activo') }}</option>
                                <option value="inactivo" @selected($filterStatus === 'inactivo')>{{ __('Inactivo') }}</option>
                            </select>
                        </flux:field>
                    </div>

                    {{-- Verification Filter --}}
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Verificación') }}</flux:label>
                            <select wire:model.live="filterVerification" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todas las suscripciones') }}</option>
                                <option value="verificado" @selected($filterVerification === 'verificado')>{{ __('Verificado') }}</option>
                                <option value="no-verificado" @selected($filterVerification === 'no-verificado')>{{ __('No Verificado') }}</option>
                            </select>
                        </flux:field>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay wire:target="search,sortBy,updatedFilterProgram,updatedFilterStatus,updatedFilterVerification" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Subscriptions Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedFilterProgram,updatedFilterStatus,updatedFilterVerification" class="animate-fade-in" style="animation-delay: 0.3s;">
        @if($this->subscriptions->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay suscripciones')"
                :description="__('No se encontraron suscripciones que coincidan con los filtros aplicados.')"
                icon="envelope"
            />
        @else
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
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
                                    {{ __('Programas') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Estado') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Verificación') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('subscribed_at')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Fecha Suscripción') }}
                                        @if($sortField === 'subscribed_at')
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
                            @foreach($this->subscriptions as $subscription)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4">
                                        <a 
                                            href="{{ route('admin.newsletter.show', $subscription) }}" 
                                            class="text-sm font-medium text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300"
                                            wire:navigate
                                        >
                                            {{ $subscription->email }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $subscription->name ?: '-' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @if($subscription->programs && count($subscription->programs) > 0)
                                                @foreach($subscription->programs as $programCode)
                                                    @php
                                                        $program = $this->programs->firstWhere('code', $programCode);
                                                        $programName = $program ? $program->name : $programCode;
                                                    @endphp
                                                    <x-ui.badge variant="neutral" size="sm">
                                                        {{ $programName }}
                                                    </x-ui.badge>
                                                @endforeach
                                            @else
                                                <span class="text-sm text-zinc-400 dark:text-zinc-500">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-ui.badge :variant="$this->getStatusBadge($subscription)" size="sm">
                                            {{ $subscription->is_active ? __('Activo') : __('Inactivo') }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-ui.badge :variant="$this->getVerificationBadge($subscription)" size="sm">
                                            {{ $subscription->isVerified() ? __('Verificado') : __('No Verificado') }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $subscription->subscribed_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.newsletter.show', $subscription) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles de la suscripción')"
                                            />

                                            {{-- Delete --}}
                                            @can('delete', $subscription)
                                                <flux:button 
                                                    wire:click="confirmDelete({{ $subscription->id }})" 
                                                    variant="ghost" 
                                                    size="sm"
                                                    icon="trash"
                                                    :label="__('common.actions.delete')"
                                                    :tooltip="__('Eliminar suscripción')"
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
                    {{ $this->subscriptions->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-newsletter-subscription" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Suscripción') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta suscripción?') }}
                @if($subscriptionToDelete)
                    @php
                        $subscription = \App\Models\NewsletterSubscription::find($subscriptionToDelete);
                    @endphp
                    <br>
                    <strong>{{ $subscription?->email }}</strong>
                @endif
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
