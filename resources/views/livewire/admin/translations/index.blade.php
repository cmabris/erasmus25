<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Traducciones') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona las traducciones de contenido en múltiples idiomas') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.translations.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Traducción') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('common.nav.translations'), 'icon' => 'language'],
            ]"
        />
    </div>

    {{-- Filters and Search --}}
    <div class="mb-6 animate-fade-in" style="animation-delay: 0.1s;">
        <x-ui.card>
            <div class="flex flex-col gap-4">
                {{-- First Row: Search and Reset --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    {{-- Search --}}
                    <div class="flex-1">
                        <x-ui.search-input 
                            wire:model.live.debounce.300ms="search"
                            :placeholder="__('Buscar por campo o valor...')"
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

                {{-- Second Row: Model, Language, Translatable ID Filters --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {{-- Model Filter --}}
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Modelo') }}</flux:label>
                            <select wire:model.live="filterModel" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todos los modelos') }}</option>
                                @foreach($this->getAvailableModels() as $modelClass => $modelName)
                                    <option value="{{ $modelClass }}" @selected($filterModel === $modelClass)>
                                        {{ $modelName }}
                                    </option>
                                @endforeach
                            </select>
                        </flux:field>
                    </div>

                    {{-- Language Filter --}}
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Idioma') }}</flux:label>
                            <select wire:model.live="filterLanguageId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todos los idiomas') }}</option>
                                @foreach($this->getLanguages() as $language)
                                    <option value="{{ $language->id }}" @selected($filterLanguageId == $language->id)>
                                        {{ $language->name }} ({{ $language->code }})
                                    </option>
                                @endforeach
                            </select>
                        </flux:field>
                    </div>

                    {{-- Translatable ID Filter (dynamic based on model) --}}
                    @if($filterModel)
                        <div>
                            <flux:field>
                                <flux:label>{{ __('Registro') }}</flux:label>
                                <select wire:model.live="filterTranslatableId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                    <option value="">{{ __('Todos los registros') }}</option>
                                    @foreach($this->getTranslatableOptions() as $option)
                                        <option value="{{ $option->id }}" @selected($filterTranslatableId == $option->id)>
                                            @if($filterModel === \App\Models\Program::class)
                                                {{ $option->code }} - {{ $option->name }}
                                            @elseif($filterModel === \App\Models\Setting::class)
                                                {{ $option->key }}
                                            @else
                                                {{ $option->id }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </flux:field>
                        </div>
                    @endif
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay wire:target="search,sortBy,updatedFilterModel,updatedFilterLanguageId,updatedFilterTranslatableId" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Translations Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedFilterModel,updatedFilterLanguageId,updatedFilterTranslatableId" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->translations->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay traducciones')"
                :description="__('No se encontraron traducciones que coincidan con los filtros aplicados.')"
                icon="language"
                :action="__('Crear Traducción')"
                :actionHref="route('admin.translations.create')"
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
                                        wire:click="sortBy('translatable_type')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Modelo') }}
                                        @if($sortField === 'translatable_type')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Registro') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('field')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Campo') }}
                                        @if($sortField === 'field')
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
                                        wire:click="sortBy('language_id')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Idioma') }}
                                        @if($sortField === 'language_id')
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
                            @foreach($this->translations as $translation)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <flux:badge variant="info">
                                            {{ $this->getModelTypeDisplayName($translation->translatable_type) }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-4">
                                        @php
                                            $displayName = $this->getTranslatableDisplayName($translation);
                                            $isDeleted = $this->isTranslatableDeleted($translation);
                                            $tooltip = $this->getTranslatableTooltip($translation);
                                        @endphp
                                        <flux:tooltip content="{{ $tooltip }}" position="top">
                                            <div class="max-w-xs truncate text-sm {{ $isDeleted ? 'text-zinc-500 dark:text-zinc-400 italic' : 'text-zinc-900 dark:text-white' }}">
                                                @if($isDeleted)
                                                    <x-ui.badge variant="danger" size="sm" class="mr-2">
                                                        {{ __('Eliminado') }}
                                                    </x-ui.badge>
                                                @endif
                                                {{ $displayName }}
                                            </div>
                                        </flux:tooltip>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <code class="rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200">
                                            {{ $translation->field }}
                                        </code>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $translation->language->name ?? '-' }}
                                            </span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                ({{ $translation->language->code ?? '-' }})
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="max-w-md truncate text-sm text-zinc-900 dark:text-white" title="{{ $translation->value }}">
                                            {{ \Illuminate\Support\Str::limit($translation->value, 50) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $translation->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            @can('view', $translation)
                                                <flux:button 
                                                    href="{{ route('admin.translations.show', $translation) }}" 
                                                    variant="ghost"
                                                    size="sm"
                                                    wire:navigate
                                                    icon="eye"
                                                >
                                                    {{ __('Ver') }}
                                                </flux:button>
                                            @endcan
                                            @can('update', $translation)
                                                <flux:button 
                                                    href="{{ route('admin.translations.edit', $translation) }}" 
                                                    variant="ghost"
                                                    size="sm"
                                                    wire:navigate
                                                    icon="pencil"
                                                >
                                                    {{ __('Editar') }}
                                                </flux:button>
                                            @endcan
                                            @can('delete', $translation)
                                                <flux:button 
                                                    variant="ghost"
                                                    size="sm"
                                                    wire:click="confirmDelete({{ $translation->id }})"
                                                    icon="trash"
                                                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                >
                                                    {{ __('Eliminar') }}
                                                </flux:button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-6 border-t border-zinc-200 px-4 py-4 dark:border-zinc-700 sm:px-6">
                    {{ $this->translations->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-translation" wire:model="showDeleteModal">
        <form wire:submit="delete">
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ __('Eliminar Traducción') }}
                    </h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('¿Estás seguro de que deseas eliminar esta traducción? Esta acción no se puede deshacer.') }}
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button 
                        type="button"
                        variant="ghost" 
                        wire:click="$set('showDeleteModal', false)"
                    >
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button 
                        type="submit"
                        variant="danger"
                    >
                        {{ __('Eliminar') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
