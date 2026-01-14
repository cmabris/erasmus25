<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $resolution->title }}
                    </h1>
                    @if($resolution->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @elseif($resolution->published_at)
                        <x-ui.badge variant="success" size="lg" icon="eye">
                            {{ __('Publicada') }}
                        </x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral" size="lg">
                            {{ __('Borrador') }}
                        </x-ui.badge>
                    @endif
                    <x-ui.badge :variant="$this->getTypeColor($resolution->type)" size="lg">
                        {{ $this->getTypeLabel($resolution->type) }}
                    </x-ui.badge>
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Convocatoria') }}: <strong>{{ $call->title }}</strong>
                    @if($resolution->callPhase)
                        · {{ __('Fase') }}: <strong>{{ $resolution->callPhase->name }}</strong>
                    @endif
                    @if($resolution->official_date)
                        · {{ __('Fecha oficial') }}: <strong>{{ $resolution->official_date->format('d/m/Y') }}</strong>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$resolution->trashed())
                    @can('update', $resolution)
                        <flux:button 
                            href="{{ route('admin.calls.resolutions.edit', [$call, $resolution]) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endcan
                @endif
                <flux:button 
                    href="{{ route('admin.calls.resolutions.index', $call) }}" 
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
                ['label' => __('common.nav.calls'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
                ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'megaphone'],
                ['label' => __('common.nav.resolutions'), 'href' => route('admin.calls.resolutions.index', $call), 'icon' => 'document-check'],
                ['label' => $resolution->title, 'icon' => 'document-check'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Resolution Information --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Información de la Resolución') }}</flux:heading>
                    <div class="space-y-4">
                        @if($resolution->description)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Descripción') }}</p>
                                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $resolution->description }}</p>
                            </div>
                        @endif

                        @if($resolution->evaluation_procedure)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Procedimiento de Evaluación') }}</p>
                                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $resolution->evaluation_procedure }}</p>
                            </div>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tipo') }}</p>
                                <p class="mt-1">
                                    <x-ui.badge :variant="$this->getTypeColor($resolution->type)" size="sm">
                                        {{ $this->getTypeLabel($resolution->type) }}
                                    </x-ui.badge>
                                </p>
                            </div>
                            @if($resolution->official_date)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha Oficial') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $resolution->official_date->format('d/m/Y') }}
                                    </p>
                                </div>
                            @endif
                            @if($resolution->published_at)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha de Publicación') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $resolution->published_at->format('d/m/Y') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- PDF Section --}}
            @if($this->hasPdf())
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Documento PDF') }}</flux:heading>
                        <div class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <flux:icon name="document-text" class="[:where(&)]:size-12 text-red-600 dark:text-red-400" variant="outline" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->existingPdf->file_name }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ number_format($this->existingPdf->size / 1024, 2) }} KB
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <flux:button 
                                    variant="primary"
                                    size="sm"
                                    href="{{ $this->existingPdf->getUrl() }}"
                                    target="_blank"
                                    icon="eye"
                                >
                                    {{ __('Ver PDF') }}
                                </flux:button>
                                <flux:button 
                                    variant="ghost"
                                    size="sm"
                                    href="{{ $this->existingPdf->getUrl() }}"
                                    download
                                    icon="arrow-down-tray"
                                >
                                    {{ __('Descargar') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @else
                <x-ui.card>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:icon name="document-text" class="[:where(&)]:size-12 mx-auto text-zinc-400" variant="outline" />
                        <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No hay PDF asociado') }}</p>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Esta resolución no tiene un documento PDF asociado') }}</p>
                    </div>
                </x-ui.card>
            @endif

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

            {{-- Phase Information --}}
            @if($resolution->callPhase)
                <x-ui.card>
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="md">{{ __('Información de la Fase') }}</flux:heading>
                            <flux:button 
                                href="{{ route('admin.calls.phases.show', [$call, $resolution->callPhase]) }}" 
                                variant="ghost" 
                                size="sm"
                                wire:navigate
                            >
                                {{ __('Ver fase') }}
                            </flux:button>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nombre') }}</p>
                                <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $resolution->callPhase->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Orden') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $resolution->callPhase->order }}</p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Resolution Details --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                    </div>

                    <div class="space-y-3">
                        @if($resolution->created_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Creada') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $resolution->created_at->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        @endif

                        @if($resolution->creator)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Creada por') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $resolution->creator->name }}
                                </p>
                            </div>
                        @endif

                        @if($resolution->updated_at && $resolution->updated_at->ne($resolution->created_at))
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actualizada') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $resolution->updated_at->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        @endif

                        @if($resolution->trashed())
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Eliminada') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $resolution->deleted_at->translatedFormat('d M Y H:i') }}
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
                        @if(!$resolution->trashed())
                            {{-- Publish/Unpublish --}}
                            @can('publish', $resolution)
                                @if($resolution->published_at)
                                    <flux:button 
                                        wire:click="unpublish"
                                        variant="ghost"
                                        icon="eye-slash"
                                        wire:loading.attr="disabled"
                                        wire:target="unpublish"
                                        class="w-full"
                                    >
                                        <span wire:loading.remove wire:target="unpublish">
                                            {{ __('Despublicar') }}
                                        </span>
                                        <span wire:loading wire:target="unpublish">
                                            {{ __('Despublicando...') }}
                                        </span>
                                    </flux:button>
                                @else
                                    <flux:button 
                                        wire:click="publish"
                                        variant="ghost"
                                        icon="paper-airplane"
                                        wire:loading.attr="disabled"
                                        wire:target="publish"
                                        class="w-full"
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

                            {{-- Edit --}}
                            @can('update', $resolution)
                                <flux:button 
                                    href="{{ route('admin.calls.resolutions.edit', [$call, $resolution]) }}" 
                                    variant="ghost"
                                    wire:navigate
                                    icon="pencil"
                                    class="w-full"
                                >
                                    {{ __('common.actions.edit') }}
                                </flux:button>
                            @endcan

                            {{-- Delete --}}
                            @can('delete', $resolution)
                                <flux:button 
                                    wire:click="$set('showDeleteModal', true)"
                                    variant="ghost"
                                    icon="trash"
                                    class="w-full"
                                >
                                    {{ __('common.actions.delete') }}
                                </flux:button>
                            @endcan
                        @else
                            {{-- Restore --}}
                            @can('restore', $resolution)
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)"
                                    variant="ghost"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('common.actions.restore') }}
                                </flux:button>
                            @endcan

                            {{-- Force Delete --}}
                            @can('forceDelete', $resolution)
                                <flux:button 
                                    wire:click="$set('showForceDeleteModal', true)"
                                    variant="ghost"
                                    icon="trash"
                                    class="w-full"
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
    <flux:modal name="delete-resolution" :show="$showDeleteModal" wire:model="showDeleteModal">
        <form wire:submit="delete" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Resolución') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar esta resolución? Esta acción puede revertirse.') }}
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
    <flux:modal name="restore-resolution" :show="$showRestoreModal" wire:model="showRestoreModal">
        <form wire:submit="restore" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Restaurar Resolución') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas restaurar esta resolución?') }}
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
    <flux:modal name="force-delete-resolution" :show="$showForceDeleteModal" wire:model="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Permanentemente') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar permanentemente esta resolución? Esta acción no se puede revertir.') }}
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
    <x-ui.toast event="resolution-published" variant="success" />
    <x-ui.toast event="resolution-unpublished" variant="success" />
    <x-ui.toast event="resolution-deleted" variant="success" />
    <x-ui.toast event="resolution-restored" variant="success" />
    <x-ui.toast event="resolution-force-deleted" variant="success" />
</div>
