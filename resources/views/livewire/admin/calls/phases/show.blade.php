<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $callPhase->name }}
                    </h1>
                    @if($callPhase->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @elseif($callPhase->is_current)
                        <x-ui.badge variant="success" size="lg" icon="star">
                            {{ __('Fase Actual') }}
                        </x-ui.badge>
                    @endif
                    <x-ui.badge :variant="$this->getPhaseTypeColor($callPhase->phase_type)" size="lg">
                        {{ $this->getPhaseTypeLabel($callPhase->phase_type) }}
                    </x-ui.badge>
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Convocatoria') }}: <strong>{{ $call->title }}</strong>
                    @if($callPhase->start_date || $callPhase->end_date)
                        ·
                        @if($callPhase->start_date)
                            {{ __('Desde') }}: <strong>{{ $callPhase->start_date->format('d/m/Y') }}</strong>
                        @endif
                        @if($callPhase->end_date)
                            {{ __('Hasta') }}: <strong>{{ $callPhase->end_date->format('d/m/Y') }}</strong>
                        @endif
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$callPhase->trashed())
                    @can('update', $callPhase)
                        <flux:button 
                            href="{{ route('admin.calls.phases.edit', [$call, $callPhase]) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endcan
                @endif
                <flux:button 
                    href="{{ route('admin.calls.phases.index', $call) }}" 
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
                ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'document'],
                ['label' => __('Fases'), 'href' => route('admin.calls.phases.index', $call), 'icon' => 'list-bullet'],
                ['label' => $callPhase->name, 'icon' => 'list-bullet'],
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
                    </div>
                </div>
            </x-ui.card>

            {{-- Phase Information --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Información de la Fase') }}</flux:heading>
                    <div class="space-y-4">
                        @if($callPhase->description)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Descripción') }}</p>
                                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $callPhase->description }}</p>
                            </div>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tipo de Fase') }}</p>
                                <p class="mt-1">
                                    <x-ui.badge :variant="$this->getPhaseTypeColor($callPhase->phase_type)" size="sm">
                                        {{ $this->getPhaseTypeLabel($callPhase->phase_type) }}
                                    </x-ui.badge>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Orden') }}</p>
                                <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $callPhase->order }}</p>
                            </div>
                            @if($callPhase->start_date)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha de Inicio') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $callPhase->start_date->format('d/m/Y') }}
                                    </p>
                                </div>
                            @endif
                            @if($callPhase->end_date)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha de Fin') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $callPhase->end_date->format('d/m/Y') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Call Information --}}
            <x-ui.card>
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="md">{{ __('Información de la Convocatoria') }}</flux:heading>
                        <flux:button 
                            href="{{ route('admin.calls.show', $call) }}" 
                            variant="ghost" 
                            size="sm"
                            wire:navigate
                        >
                            {{ __('Ver convocatoria') }}
                        </flux:button>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Título') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->title }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Programa') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->program->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Año Académico') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->academicYear->year ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Estado') }}</p>
                            <p class="mt-1">
                                <x-ui.badge variant="info" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $call->status)) }}
                                </x-ui.badge>
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Resolutions Section --}}
            @if($callPhase->resolutions->isNotEmpty())
                <x-ui.card>
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="md">{{ __('Resoluciones Asociadas') }}</flux:heading>
                            {{-- TODO: Añadir enlace cuando se cree el CRUD de resoluciones --}}
                        </div>
                        <div class="space-y-3">
                            @foreach($callPhase->resolutions as $resolution)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-white">{{ $resolution->title }}</p>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ __('Tipo') }}: {{ ucfirst($resolution->type) }}
                                                @if($resolution->official_date)
                                                    · {{ __('Fecha oficial') }}: {{ $resolution->official_date->format('d/m/Y') }}
                                                @endif
                                            </p>
                                        </div>
                                        @if($resolution->published_at)
                                            <x-ui.badge variant="success" size="sm">
                                                {{ __('Publicada') }}
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral" size="sm">
                                                {{ __('Borrador') }}
                                            </x-ui.badge>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @else
                <x-ui.card>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:icon name="document-check" class="[:where(&)]:size-12 mx-auto text-zinc-400" variant="outline" />
                        <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No hay resoluciones asociadas') }}</p>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Esta fase aún no tiene resoluciones publicadas') }}</p>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Phase Details --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información de la Fase') }}</flux:heading>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nombre') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $callPhase->name }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tipo') }}</p>
                            <p class="mt-1">
                                <x-ui.badge :variant="$this->getPhaseTypeColor($callPhase->phase_type)" size="sm">
                                    {{ $this->getPhaseTypeLabel($callPhase->phase_type) }}
                                </x-ui.badge>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Orden') }}</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $callPhase->order }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fase Actual') }}</p>
                            <div class="mt-1">
                                @if($callPhase->is_current)
                                    <x-ui.badge variant="success" icon="star">{{ __('Sí') }}</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral">{{ __('No') }}</x-ui.badge>
                                @endif
                            </div>
                        </div>

                        @if($callPhase->start_date)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha de Inicio') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $callPhase->start_date->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if($callPhase->end_date)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha de Fin') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $callPhase->end_date->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if($callPhase->created_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Creado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $callPhase->created_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if($callPhase->updated_at && $callPhase->updated_at->ne($callPhase->created_at))
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $callPhase->updated_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if($callPhase->trashed())
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Eliminado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $callPhase->deleted_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Actions --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Acciones') }}</flux:heading>
                    </div>

                    <div class="flex flex-col gap-2">
                        @if($callPhase->trashed())
                            @can('restore', $callPhase)
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)"
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('common.actions.restore') }}
                                </flux:button>
                            @endcan
                        @else
                            @can('update', $callPhase)
                                @if($callPhase->is_current)
                                    <flux:button 
                                        wire:click="unmarkAsCurrent"
                                        variant="filled"
                                        icon="x-circle"
                                        class="w-full"
                                        wire:loading.attr="disabled"
                                        wire:target="unmarkAsCurrent"
                                    >
                                        <span wire:loading.remove wire:target="unmarkAsCurrent">
                                            {{ __('Desmarcar como Actual') }}
                                        </span>
                                        <span wire:loading wire:target="unmarkAsCurrent">
                                            {{ __('Desmarcando...') }}
                                        </span>
                                    </flux:button>
                                @else
                                    <flux:button 
                                        wire:click="markAsCurrent"
                                        variant="primary"
                                        icon="star"
                                        class="w-full"
                                        wire:loading.attr="disabled"
                                        wire:target="markAsCurrent"
                                    >
                                        <span wire:loading.remove wire:target="markAsCurrent">
                                            {{ __('Marcar como Actual') }}
                                        </span>
                                        <span wire:loading wire:target="markAsCurrent">
                                            {{ __('Marcando...') }}
                                        </span>
                                    </flux:button>
                                @endif
                            @endcan
                        @endif

                        @can('delete', $callPhase)
                            @if($callPhase->trashed())
                                @can('forceDelete', $callPhase)
                                    <flux:button 
                                        wire:click="$set('showForceDeleteModal', true)"
                                        variant="danger"
                                        icon="trash"
                                        class="w-full"
                                        :disabled="!$this->canDelete()"
                                        :title="!$this->canDelete() ? __('No se puede eliminar porque tiene resoluciones asociadas') : ''"
                                    >
                                        {{ __('common.actions.permanently_delete') }}
                                    </flux:button>
                                @endcan
                            @else
                                <flux:button 
                                    wire:click="$set('showDeleteModal', true)"
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                    :disabled="!$this->canDelete()"
                                    :title="!$this->canDelete() ? __('No se puede eliminar porque tiene resoluciones asociadas') : ''"
                                >
                                    {{ __('common.actions.delete') }}
                                </flux:button>
                            @endif
                        @endcan
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-phase" :show="$showDeleteModal" wire:model="showDeleteModal">
        <form wire:submit="delete" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Fase') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar esta fase? Esta acción puede revertirse.') }}
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="$set('showDeleteModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="danger"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.delete') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Restore Confirmation Modal --}}
    <flux:modal name="restore-phase" :show="$showRestoreModal" wire:model="showRestoreModal">
        <form wire:submit="restore" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Restaurar Fase') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas restaurar esta fase?') }}
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="$set('showRestoreModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="primary"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.restore') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Confirmation Modal --}}
    <flux:modal name="force-delete-phase" :show="$showForceDeleteModal" wire:model="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Permanentemente') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar permanentemente esta fase? Esta acción no se puede revertir.') }}
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="$set('showForceDeleteModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="danger"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.permanently_delete') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="phase-updated" variant="success" />
    <x-ui.toast event="phase-deleted" variant="success" />
    <x-ui.toast event="phase-restored" variant="success" />
    <x-ui.toast event="phase-force-deleted" variant="success" />
    <x-ui.toast event="phase-delete-error" variant="danger" />
    <x-ui.toast event="phase-force-delete-error" variant="danger" />
</div>
