<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $call->title }}
                    </h1>
                    @if($call->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @else
                        <x-ui.badge :variant="$this->getStatusColor($call->status)" size="lg">
                            {{ ucfirst(str_replace('_', ' ', $call->status)) }}
                        </x-ui.badge>
                    @endif
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Programa') }}: <strong>{{ $call->program->name ?? '-' }}</strong>
                    · {{ __('Año Académico') }}: <strong>{{ $call->academicYear->year ?? '-' }}</strong>
                    @if($call->published_at)
                        · {{ __('Publicada') }}: <strong>{{ $call->published_at->format('d/m/Y') }}</strong>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$call->trashed())
                    @can('publish', $call)
                        @if($call->status !== 'abierta')
                            <flux:button 
                                wire:click="publish"
                                variant="primary"
                                icon="paper-airplane"
                                wire:loading.attr="disabled"
                                wire:target="publish"
                            >
                                <span wire:loading.remove wire:target="publish">
                                    {{ __('Publicar') }}
                                </span>
                                <span wire:loading wire:target="publish">
                                    {{ __('Publicando...') }}
                                </span>
                            </flux:button>
                        @endif
                    @endcan
                    @can('update', $call)
                        <flux:button 
                            href="{{ route('admin.calls.edit', $call) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endcan
                @endif
                <flux:button 
                    href="{{ route('admin.calls.index') }}" 
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
                ['label' => __('Convocatorias'), 'href' => route('admin.calls.index'), 'icon' => 'document-text'],
                ['label' => $call->title, 'icon' => 'document-text'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Statistics --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Estadísticas') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                    <flux:icon name="calendar" class="[:where(&)]:size-5 text-blue-600 dark:text-blue-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Fases') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_phases'] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                                    <flux:icon name="document-check" class="[:where(&)]:size-5 text-green-600 dark:text-green-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Resoluciones') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_resolutions'] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                                    <flux:icon name="clipboard-document-list" class="[:where(&)]:size-5 text-purple-600 dark:text-purple-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Aplicaciones') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_applications'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Basic Information --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Información Básica') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tipo') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $call->type === 'alumnado' ? __('Alumnado') : __('Personal') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Modalidad') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $call->modality === 'corta' ? __('Corta') : __('Larga') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Número de Plazas') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $call->number_of_places }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Estado') }}</p>
                            <p class="mt-1">
                                <x-ui.badge :variant="$this->getStatusColor($call->status)" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $call->status)) }}
                                </x-ui.badge>
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Destinations --}}
            @if($call->destinations && count($call->destinations) > 0)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Destinos') }}</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach($call->destinations as $destination)
                                <x-ui.badge variant="info" size="sm">
                                    {{ $destination }}
                                </x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Dates --}}
            @if($call->estimated_start_date || $call->estimated_end_date)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Fechas Estimadas') }}</flux:heading>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($call->estimated_start_date)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha Inicio') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $call->estimated_start_date->format('d/m/Y') }}
                                    </p>
                                </div>
                            @endif
                            @if($call->estimated_end_date)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha Fin') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $call->estimated_end_date->format('d/m/Y') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Content Fields --}}
            @if($call->requirements || $call->documentation || $call->selection_criteria)
                <x-ui.card>
                    <div class="space-y-6">
                        @if($call->requirements)
                            <div>
                                <flux:heading size="sm" class="mb-2">{{ __('Requisitos') }}</flux:heading>
                                <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $call->requirements }}</p>
                            </div>
                        @endif
                        @if($call->documentation)
                            <div>
                                <flux:heading size="sm" class="mb-2">{{ __('Documentación') }}</flux:heading>
                                <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $call->documentation }}</p>
                            </div>
                        @endif
                        @if($call->selection_criteria)
                            <div>
                                <flux:heading size="sm" class="mb-2">{{ __('Criterios de Selección') }}</flux:heading>
                                <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $call->selection_criteria }}</p>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @endif

            {{-- Scoring Table --}}
            @if($call->scoring_table && count($call->scoring_table) > 0)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Baremo de Evaluación') }}</flux:heading>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            {{ __('Concepto') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            {{ __('Puntos Máximos') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            {{ __('Descripción') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($call->scoring_table as $item)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                                {{ $item['concept'] ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                                {{ $item['max_points'] ?? 0 }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                                {{ $item['description'] ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Phases Section --}}
            <x-ui.card>
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="md">{{ __('Fases de la Convocatoria') }}</flux:heading>
                        <div class="flex items-center gap-2">
                            @can('viewAny', \App\Models\CallPhase::class)
                                <flux:button 
                                    href="{{ route('admin.calls.phases.index', $call) }}" 
                                    variant="ghost" 
                                    size="sm"
                                    icon="list-bullet"
                                    wire:navigate
                                >
                                    {{ __('Gestionar Fases') }}
                                </flux:button>
                            @endcan
                            @can('create', \App\Models\CallPhase::class)
                                <flux:button 
                                    href="{{ route('admin.calls.phases.create', $call) }}" 
                                    variant="primary" 
                                    size="sm"
                                    icon="plus"
                                    wire:navigate
                                >
                                    {{ __('Añadir Fase') }}
                                </flux:button>
                            @endcan
                        </div>
                    </div>
                    @if($call->phases->isEmpty())
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-800">
                            <flux:icon name="calendar" class="[:where(&)]:size-12 mx-auto text-zinc-400" variant="outline" />
                            <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No hay fases registradas') }}</p>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Añade fases para gestionar el proceso de la convocatoria') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($call->phases as $phase)
                                <div class="rounded-lg border {{ $phase->is_current ? 'border-erasmus-500 bg-erasmus-50 dark:border-erasmus-400 dark:bg-erasmus-900/20' : 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800' }} p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-semibold text-zinc-900 dark:text-white">{{ $phase->name }}</h4>
                                                @if($phase->is_current)
                                                    <x-ui.badge variant="success" size="sm" icon="star">
                                                        {{ __('Fase Actual') }}
                                                    </x-ui.badge>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ ucfirst(str_replace('_', ' ', $phase->phase_type)) }}</p>
                                            @if($phase->description)
                                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">{{ $phase->description }}</p>
                                            @endif
                                            <div class="mt-2 flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                                                @if($phase->start_date)
                                                    <span>{{ __('Inicio') }}: {{ $phase->start_date->format('d/m/Y') }}</span>
                                                @endif
                                                @if($phase->end_date)
                                                    <span>{{ __('Fin') }}: {{ $phase->end_date->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @can('update', $phase)
                                                @if(!$phase->is_current)
                                                    <flux:button 
                                                        wire:click="markPhaseAsCurrent({{ $phase->id }})"
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="star"
                                                        :label="__('Marcar como actual')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="markPhaseAsCurrent({{ $phase->id }})"
                                                    />
                                                @else
                                                    <flux:button 
                                                        wire:click="unmarkPhaseAsCurrent({{ $phase->id }})"
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="star"
                                                        :label="__('Desmarcar')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="unmarkPhaseAsCurrent({{ $phase->id }})"
                                                    />
                                                @endif
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Resolutions Section --}}
            <x-ui.card>
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="md">{{ __('Resoluciones') }}</flux:heading>
                        @can('create', \App\Models\Resolution::class)
                            <flux:button 
                                href="{{ route('admin.calls.index') }}" 
                                variant="ghost" 
                                size="sm"
                                icon="plus"
                                wire:navigate
                            >
                                {{ __('Añadir Resolución') }}
                            </flux:button>
                        @endcan
                    </div>
                    @if($call->resolutions->isEmpty())
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-800">
                            <flux:icon name="document-check" class="[:where(&)]:size-12 mx-auto text-zinc-400" variant="outline" />
                            <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No hay resoluciones registradas') }}</p>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Añade resoluciones para publicar los resultados de la convocatoria') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($call->resolutions as $resolution)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-semibold text-zinc-900 dark:text-white">{{ $resolution->title }}</h4>
                                                <x-ui.badge variant="info" size="sm">
                                                    {{ ucfirst($resolution->type) }}
                                                </x-ui.badge>
                                                @if($resolution->published_at)
                                                    <x-ui.badge variant="success" size="sm">
                                                        {{ __('Publicada') }}
                                                    </x-ui.badge>
                                                @endif
                                            </div>
                                            @if($resolution->description)
                                                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $resolution->description }}</p>
                                            @endif
                                            <div class="mt-2 flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                                                @if($resolution->official_date)
                                                    <span>{{ __('Fecha oficial') }}: {{ $resolution->official_date->format('d/m/Y') }}</span>
                                                @endif
                                                @if($resolution->published_at)
                                                    <span>{{ __('Publicada') }}: {{ $resolution->published_at->format('d/m/Y H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if(!$resolution->published_at)
                                                <flux:button 
                                                    wire:click="publishResolution({{ $resolution->id }})"
                                                    variant="ghost" 
                                                    size="sm"
                                                    icon="paper-airplane"
                                                    :label="__('Publicar')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="publishResolution({{ $resolution->id }})"
                                                />
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Call Details --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información de la Convocatoria') }}</flux:heading>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Programa') }}</p>
                            <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $call->program->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Año Académico') }}</p>
                            <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $call->academicYear->year ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Creada') }}</p>
                            <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $call->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($call->updated_at && $call->updated_at->ne($call->created_at))
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actualizada') }}</p>
                                <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $call->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                        @if($call->creator)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Creada por') }}</p>
                                <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $call->creator->name }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Status Actions --}}
            @if(!$call->trashed())
                <x-ui.card>
                    <div class="space-y-4">
                        <div>
                            <flux:heading size="sm">{{ __('Cambiar Estado') }}</flux:heading>
                        </div>

                        <div class="space-y-2">
                            @foreach(['borrador', 'abierta', 'cerrada', 'en_baremacion', 'resuelta', 'archivada'] as $status)
                                @if($status !== $call->status)
                                    <flux:button 
                                        wire:click="changeStatus('{{ $status }}')"
                                        variant="ghost" 
                                        size="sm"
                                        class="w-full justify-start"
                                        wire:loading.attr="disabled"
                                        wire:target="changeStatus('{{ $status }}')"
                                    >
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </flux:button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Actions --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Acciones') }}</flux:heading>
                    </div>

                    <div class="flex flex-col gap-2">
                        @if(!$call->trashed())
                            @can('update', $call)
                                <flux:button 
                                    href="{{ route('admin.calls.edit', $call) }}" 
                                    variant="primary"
                                    wire:navigate
                                    icon="pencil"
                                    class="w-full"
                                >
                                    {{ __('common.actions.edit') }}
                                </flux:button>
                            @endcan
                            @can('delete', $call)
                                @if($this->canDelete())
                                    <flux:button 
                                        wire:click="$set('showDeleteModal', true)"
                                        variant="ghost"
                                        icon="trash"
                                        class="w-full text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        {{ __('common.actions.delete') }}
                                    </flux:button>
                                @endif
                            @endcan
                        @else
                            @can('restore', $call)
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)"
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('common.actions.restore') }}
                                </flux:button>
                            @endcan
                            @can('forceDelete', $call)
                                <flux:button 
                                    wire:click="$set('showForceDeleteModal', true)"
                                    variant="ghost"
                                    icon="trash"
                                    class="w-full text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    {{ __('common.actions.permanently_delete') }}
                                </flux:button>
                            @endcan
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-call" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Convocatoria') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta convocatoria?') }}
                <br>
                <strong>{{ $call->title }}</strong>
            </flux:text>
            @if(!$this->canDelete())
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar esta convocatoria porque tiene relaciones activas (fases, resoluciones o aplicaciones).') }}
                    </flux:callout.text>
                </flux:callout>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará la convocatoria como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
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

    {{-- Restore Confirmation Modal --}}
    <flux:modal name="restore-call" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Convocatoria') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar esta convocatoria?') }}
                <br>
                <strong>{{ $call->title }}</strong>
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
                        {{ __('common.actions.restore') }}
                    </span>
                    <span wire:loading wire:target="restore">
                        {{ __('Restaurando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Confirmation Modal --}}
    <flux:modal name="force-delete-call" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente esta convocatoria?') }}
                <br>
                <strong>{{ $call->title }}</strong>
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). La convocatoria se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($this->hasRelationships())
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar permanentemente esta convocatoria porque tiene relaciones activas (fases, resoluciones o aplicaciones). Primero debes eliminar o reasignar estas relaciones.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if(!$this->hasRelationships())
                    <flux:button 
                        type="submit" 
                        variant="danger"
                        wire:loading.attr="disabled"
                        wire:target="forceDelete"
                    >
                        <span wire:loading.remove wire:target="forceDelete">
                            {{ __('common.actions.permanently_delete') }}
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
    <x-ui.toast event="call-updated" variant="success" />
    <x-ui.toast event="call-published" variant="success" />
    <x-ui.toast event="call-deleted" variant="success" />
    <x-ui.toast event="call-restored" variant="success" />
    <x-ui.toast event="call-force-deleted" variant="warning" />
    <x-ui.toast event="call-force-delete-error" variant="error" />
    <x-ui.toast event="call-delete-error" variant="error" />
    <x-ui.toast event="phase-updated" variant="success" />
    <x-ui.toast event="resolution-published" variant="success" />
</div>
