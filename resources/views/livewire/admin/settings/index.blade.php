<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Configuración del Sistema') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona las configuraciones del sistema organizadas por categorías') }}
                </p>
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Configuración del Sistema'), 'icon' => 'cog-6-tooth'],
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
                        :placeholder="__('Buscar por clave, valor o descripción...')"
                    />
                </div>

                {{-- Group Filter --}}
                <div class="sm:w-48">
                    <flux:field>
                        <flux:label>{{ __('Grupo') }}</flux:label>
                        <select wire:model.live="filterGroup" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos los grupos') }}</option>
                            @foreach($this->availableGroups as $group)
                                <option value="{{ $group }}" @selected($filterGroup === $group)>
                                    {{ $this->getGroupLabel($group) }}
                                </option>
                            @endforeach
                        </select>
                    </flux:field>
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
    <div wire:loading.delay wire:target="search,filterGroup,sortBy" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Settings by Group --}}
    <div wire:loading.remove.delay wire:target="search,filterGroup,sortBy" class="space-y-6 animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->settings->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay configuraciones')"
                :description="__('No se encontraron configuraciones que coincidan con los filtros aplicados.')"
                icon="cog-6-tooth"
            />
        @else
            @foreach($this->settings as $group => $groupSettings)
                <x-ui.card>
                    {{-- Group Header --}}
                    <div class="mb-4 flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                                {{ $this->getGroupLabel($group) }}
                            </h2>
                            <x-ui.badge :variant="$this->getGroupBadgeVariant($group)" size="sm">
                                {{ $groupSettings->count() }} {{ __('configuraciones') }}
                            </x-ui.badge>
                        </div>
                    </div>

                    {{-- Settings Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        <button 
                                            wire:click="sortBy('key')" 
                                            class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                        >
                                            {{ __('Clave') }}
                                            @if($sortField === 'key')
                                                <flux:icon 
                                                    :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                    class="[:where(&)]:size-4" 
                                                    variant="outline" 
                                                />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Valor') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Tipo') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Descripción') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Última Actualización') }}
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Acciones') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($groupSettings as $setting)
                                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded font-mono">
                                                    {{ $setting->key }}
                                                </code>
                                                @if($setting->translations_count > 0)
                                                    <flux:tooltip content="{{ __('Esta configuración tiene :count traducción(es) disponible(s)', ['count' => $setting->translations_count]) }}" position="top">
                                                        <flux:icon name="language" class="[:where(&)]:size-4 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                                    </flux:tooltip>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="max-w-md">
                                                @if($this->isValueTruncated($setting) || ($setting->type === 'json' && is_array($setting->value)))
                                                    <flux:tooltip 
                                                        :content="$this->getFullValue($setting)" 
                                                        position="top"
                                                    >
                                                        <div class="text-sm text-zinc-900 dark:text-white cursor-help">
                                                            {{ $this->formatValue($setting) }}
                                                        </div>
                                                    </flux:tooltip>
                                                    @if($setting->type === 'json' && is_array($setting->value))
                                                        <div class="mt-1">
                                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                {{ __('common.settings.messages.view_json_complete') }}
                                                            </span>
                                                        </div>
                                                    @elseif($this->isValueTruncated($setting))
                                                        <div class="mt-1">
                                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                {{ __('common.settings.messages.view_full_value') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="text-sm text-zinc-900 dark:text-white">
                                                        {{ $this->formatValue($setting) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <x-ui.badge :variant="$this->getTypeBadgeVariant($setting->type)" size="sm">
                                                {{ $this->getTypeLabel($setting->type) }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="max-w-md">
                                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                                    {{ $setting->description ?: __('Sin descripción') }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            <div>
                                                <div>{{ $setting->updated_at->format('d/m/Y H:i') }}</div>
                                                @if($setting->updater)
                                                    <div class="text-xs text-zinc-400 dark:text-zinc-500">
                                                        {{ __('por') }} {{ $setting->updater->name }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                @if($this->canEdit())
                                                    <flux:button 
                                                        href="{{ route('admin.settings.edit', $setting) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar configuración')"
                                                    />
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            @endforeach
        @endif
    </div>
</div>
