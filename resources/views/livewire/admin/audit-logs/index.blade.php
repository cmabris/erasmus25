<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Auditoría y Logs') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Visualiza el historial completo de actividades y cambios en el sistema') }}
                </p>
            </div>
            <div>
                <flux:button 
                    wire:click="export"
                    variant="primary"
                    icon="arrow-down-tray"
                >
                    {{ __('Exportar') }}
                </flux:button>
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Auditoría y Logs'), 'icon' => 'clipboard-document-list'],
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
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <x-ui.search-input 
                                    wire:model.live.debounce.500ms="search"
                                    :placeholder="__('Buscar por descripción o modelo...')"
                                />
                            </div>
                            <flux:tooltip content="{{ __('Busca en la descripción de la acción y en el tipo de modelo afectado') }}" position="top">
                                <flux:icon name="information-circle" class="[:where(&)]:size-5 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                            </flux:tooltip>
                        </div>
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Model Filter --}}
                    <div>
                        <flux:field>
                            <flux:label>
                                {{ __('Modelo') }}
                                <flux:tooltip content="{{ __('Filtra los logs por el tipo de modelo afectado (Programa, Convocatoria, Noticia, etc.)') }}" position="top">
                                    <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                </flux:tooltip>
                            </flux:label>
                            <select wire:model.live="filterModel" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todos los modelos') }}</option>
                                @foreach($this->availableModels as $modelType)
                                    <option value="{{ $modelType }}" @selected($filterModel === $modelType)>
                                        {{ $this->getModelDisplayName($modelType) }}
                                    </option>
                                @endforeach
                            </select>
                        </flux:field>
                    </div>

                    {{-- User/Causer Filter --}}
                    <div>
                        <flux:field>
                            <flux:label>
                                {{ __('Usuario') }}
                                <flux:tooltip content="{{ __('Filtra los logs por el usuario que realizó la acción') }}" position="top">
                                    <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                </flux:tooltip>
                            </flux:label>
                            <select wire:model.live="filterCauserId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todos los usuarios') }}</option>
                                @foreach($this->availableCausers as $user)
                                    <option value="{{ $user->id }}" @selected($filterCauserId == $user->id)>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </flux:field>
                    </div>

                    {{-- Description/Action Filter --}}
                    <div>
                        <flux:field>
                            <flux:label>
                                {{ __('Acción') }}
                                <flux:tooltip content="{{ __('Filtra los logs por el tipo de acción realizada (creado, actualizado, eliminado, publicado, etc.)') }}" position="top">
                                    <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                </flux:tooltip>
                            </flux:label>
                            <select wire:model.live="filterDescription" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">{{ __('Todas las acciones') }}</option>
                                @foreach($this->availableDescriptions as $description)
                                    <option value="{{ $description }}" @selected($filterDescription === $description)>
                                        {{ $this->getDescriptionDisplayName($description) }}
                                    </option>
                                @endforeach
                            </select>
                        </flux:field>
                    </div>

                    {{-- Log Name Filter (if multiple logs are used) --}}
                    @if($this->availableLogNames->isNotEmpty())
                        <div>
                            <flux:field>
                                <flux:label>{{ __('Log') }}</flux:label>
                                <select wire:model.live="filterLogName" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                    <option value="">{{ __('Todos los logs') }}</option>
                                    @foreach($this->availableLogNames as $logName)
                                        <option value="{{ $logName }}" @selected($filterLogName === $logName)>
                                            {{ $logName }}
                                        </option>
                                    @endforeach
                                </select>
                            </flux:field>
                        </div>
                    @endif
                </div>

                {{-- Third Row: Date Range Filters --}}
                <div class="space-y-4">
                    {{-- Quick Date Filters --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Filtros rápidos:') }}</span>
                        <flux:button 
                            variant="ghost" 
                            size="sm"
                            wire:click="$set('filterDateFrom', '{{ now()->subDay()->format('Y-m-d') }}'); $set('filterDateTo', '{{ now()->format('Y-m-d') }}')"
                            icon="clock"
                        >
                            {{ __('Últimas 24 horas') }}
                        </flux:button>
                        <flux:button 
                            variant="ghost" 
                            size="sm"
                            wire:click="$set('filterDateFrom', '{{ now()->subWeek()->format('Y-m-d') }}'); $set('filterDateTo', '{{ now()->format('Y-m-d') }}')"
                            icon="calendar"
                        >
                            {{ __('Última semana') }}
                        </flux:button>
                        <flux:button 
                            variant="ghost" 
                            size="sm"
                            wire:click="$set('filterDateFrom', '{{ now()->subMonth()->format('Y-m-d') }}'); $set('filterDateTo', '{{ now()->format('Y-m-d') }}')"
                            icon="calendar-days"
                        >
                            {{ __('Último mes') }}
                        </flux:button>
                    </div>

                    {{-- Date Range Filters --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {{-- Date From --}}
                        <div>
                            <flux:field>
                                <flux:label>
                                    {{ __('Desde') }}
                                    <flux:tooltip content="{{ __('Filtra los logs desde esta fecha. Deja vacío para no filtrar por fecha inicial.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    type="date"
                                    wire:model.live="filterDateFrom"
                                />
                            </flux:field>
                        </div>

                        {{-- Date To --}}
                        <div>
                            <flux:field>
                                <flux:label>
                                    {{ __('Hasta') }}
                                    <flux:tooltip content="{{ __('Filtra los logs hasta esta fecha. Deja vacío para no filtrar por fecha final.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    type="date"
                                    wire:model.live="filterDateTo"
                                />
                            </flux:field>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay wire:target="search,sortBy,updatedFilterModel,updatedFilterCauserId,updatedFilterDescription,updatedFilterLogName,updatedFilterDateFrom,updatedFilterDateTo" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Activities Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedFilterModel,updatedFilterCauserId,updatedFilterDescription,updatedFilterLogName,updatedFilterDateFrom,updatedFilterDateTo" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->activities->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay logs de auditoría')"
                :description="__('No se encontraron actividades que coincidan con los filtros aplicados.')"
                icon="clipboard-document-list"
            />
        @else
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('created_at')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Fecha/Hora') }}
                                        @if($sortField === 'created_at')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Usuario') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('description')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Acción') }}
                                        @if($sortField === 'description')
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
                                        wire:click="sortBy('subject_type')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Modelo') }}
                                        @if($sortField === 'subject_type')
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
                                    {{ __('Cambios') }}
                                </th>
                                @if($this->availableLogNames->isNotEmpty())
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('Log') }}
                                    </th>
                                @endif
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($this->activities as $activity)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    {{-- Date/Time --}}
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <div class="text-sm text-zinc-900 dark:text-white">
                                            {{ $activity->created_at->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </div>
                                    </td>

                                    {{-- User/Causer --}}
                                    <td class="px-4 py-4">
                                        @if($activity->causer)
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-erasmus-100 text-xs font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                                                    {{ $activity->causer->initials() }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                        {{ $activity->causer->name }}
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ $activity->causer->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                                {{ __('Sistema') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Description/Action --}}
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <flux:badge :variant="$this->getDescriptionBadgeVariant($activity->description)" size="sm">
                                            {{ $this->getDescriptionDisplayName($activity->description) }}
                                        </flux:badge>
                                    </td>

                                    {{-- Model/Subject Type --}}
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <flux:badge variant="info" size="sm">
                                            {{ $this->getModelDisplayName($activity->subject_type) }}
                                        </flux:badge>
                                    </td>

                                    {{-- Subject/Record --}}
                                    <td class="px-4 py-4">
                                        @php
                                            $subjectUrl = $this->getSubjectUrl($activity->subject_type, $activity->subject_id);
                                            $subjectTitle = $this->getSubjectTitle($activity->subject);
                                        @endphp
                                        @if($subjectUrl && $activity->subject)
                                            <a href="{{ $subjectUrl }}" wire:navigate class="text-sm font-medium text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300">
                                                {{ Str::limit($subjectTitle, 40) }}
                                            </a>
                                        @elseif($activity->subject)
                                            <span class="text-sm text-zinc-900 dark:text-white">
                                                {{ Str::limit($subjectTitle, 40) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                                {{ __('Registro eliminado') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Changes Summary --}}
                                    <td class="px-4 py-4">
                                        @php
                                            $changesSummary = $this->formatChangesSummary($activity->properties);
                                        @endphp
                                        @if($changesSummary !== '-')
                                            <div class="max-w-xs">
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                                    {{ Str::limit($changesSummary, 50) }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $changesSummary }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Log Name (if multiple logs) --}}
                                    @if($this->availableLogNames->isNotEmpty())
                                        <td class="whitespace-nowrap px-4 py-4">
                                            @if($activity->log_name)
                                                <flux:badge variant="neutral" size="sm">
                                                    {{ $activity->log_name }}
                                                </flux:badge>
                                            @else
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ __('default') }}
                                                </span>
                                            @endif
                                        </td>
                                    @endif

                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-4 py-4 text-right">
                                        <flux:button 
                                            href="{{ route('admin.audit-logs.show', $activity) }}" 
                                            variant="ghost"
                                            size="sm"
                                            wire:navigate
                                            icon="eye"
                                        >
                                            {{ __('Ver') }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-zinc-200 pt-6 dark:border-zinc-700 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('Mostrando') }}
                        </span>
                        <select wire:model.live="perPage" class="rounded-lg border border-zinc-300 bg-white px-2 py-1 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('por página') }}
                        </span>
                    </div>

                    <div>
                        {{ $this->activities->links() }}
                    </div>
                </div>
            </x-ui.card>
        @endif
    </div>
</div>
