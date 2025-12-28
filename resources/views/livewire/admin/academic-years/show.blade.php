<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $this->academicYear->year }}
                    </h1>
                    @if($this->academicYear->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @elseif($this->academicYear->is_current)
                        <x-ui.badge variant="success" size="lg" icon="star">
                            {{ __('Año Actual') }}
                        </x-ui.badge>
                    @endif
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Desde') }}: <strong>{{ $this->academicYear->start_date?->format('d/m/Y') ?? '-' }}</strong>
                    · {{ __('Hasta') }}: <strong>{{ $this->academicYear->end_date?->format('d/m/Y') ?? '-' }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $this->academicYear)
                    <flux:button 
                        href="{{ $this->editUrl }}" 
                        variant="primary"
                        wire:navigate
                        icon="pencil"
                    >
                        {{ __('common.actions.edit') }}
                    </flux:button>
                @endcan
                <flux:button 
                    href="{{ route('admin.academic-years.index') }}" 
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
                ['label' => __('common.nav.academic_years'), 'href' => route('admin.academic-years.index'), 'icon' => 'calendar'],
                ['label' => $this->academicYear->year, 'icon' => 'calendar'],
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
                                <div class="rounded-lg bg-erasmus-100 p-2 dark:bg-erasmus-900/30">
                                    <flux:icon name="megaphone" class="[:where(&)]:size-5 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Convocatorias') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_calls'] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                    <flux:icon name="newspaper" class="[:where(&)]:size-5 text-blue-600 dark:text-blue-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Noticias') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_news'] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                                    <flux:icon name="document-text" class="[:where(&)]:size-5 text-green-600 dark:text-green-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Documentos') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_documents'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Related Calls --}}
            @if($this->academicYear->calls->isNotEmpty())
                <x-ui.card>
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="md">{{ __('Convocatorias Relacionadas') }}</flux:heading>
                            <flux:button 
                                href="{{ route('admin.academic-years.index') }}" 
                                variant="ghost" 
                                size="sm"
                                wire:navigate
                            >
                                {{ __('Ver todas') }}
                            </flux:button>
                        </div>
                        <div class="space-y-3">
                            @foreach($this->academicYear->calls as $call)
                                @php
                                    $statusConfig = match($call->status) {
                                        'abierta' => ['variant' => 'success', 'key' => 'open'],
                                        'cerrada' => ['variant' => 'danger', 'key' => 'closed'],
                                        'en_baremacion' => ['variant' => 'warning', 'key' => 'evaluating'],
                                        'resuelta' => ['variant' => 'info', 'key' => 'resolved'],
                                        'archivada' => ['variant' => 'neutral', 'key' => 'archived'],
                                        default => ['variant' => 'neutral', 'key' => 'draft'],
                                    };
                                @endphp
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-white">{{ $call->title }}</p>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ __('Programa') }}: {{ $call->program->name ?? '-' }}
                                            </p>
                                        </div>
                                        <x-ui.badge variant="{{ $statusConfig['variant'] }}" size="sm">
                                            {{ __('common.call_status.' . $statusConfig['key']) }}
                                        </x-ui.badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Related News --}}
            @if($this->academicYear->newsPosts->isNotEmpty())
                <x-ui.card>
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="md">{{ __('Noticias Relacionadas') }}</flux:heading>
                            <flux:button 
                                href="{{ route('admin.academic-years.index') }}" 
                                variant="ghost" 
                                size="sm"
                                wire:navigate
                            >
                                {{ __('Ver todas') }}
                            </flux:button>
                        </div>
                        <div class="space-y-3">
                            @foreach($this->academicYear->newsPosts as $newsPost)
                                @php
                                    $statusConfig = match($newsPost->status) {
                                        'publicado' => ['variant' => 'success', 'key' => 'published'],
                                        'borrador' => ['variant' => 'neutral', 'key' => 'draft'],
                                        'pendiente' => ['variant' => 'warning', 'key' => 'pending'],
                                        default => ['variant' => 'neutral', 'key' => 'draft'],
                                    };
                                @endphp
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-white">{{ $newsPost->title }}</p>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $newsPost->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <x-ui.badge variant="{{ $statusConfig['variant'] }}" size="sm">
                                            {{ __('common.status.' . $statusConfig['key']) }}
                                        </x-ui.badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Related Documents --}}
            @if($this->academicYear->documents->isNotEmpty())
                <x-ui.card>
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="md">{{ __('Documentos Relacionados') }}</flux:heading>
                            <flux:button 
                                href="{{ route('admin.academic-years.index') }}" 
                                variant="ghost" 
                                size="sm"
                                wire:navigate
                            >
                                {{ __('Ver todos') }}
                            </flux:button>
                        </div>
                        <div class="space-y-3">
                            @foreach($this->academicYear->documents as $document)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-white">{{ $document->name }}</p>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ __('Categoría') }}: {{ $document->category->name ?? '-' }}
                                            </p>
                                        </div>
                                        <flux:icon name="document-arrow-down" class="[:where(&)]:size-5 text-zinc-400" variant="outline" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Academic Year Details --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información del Año Académico') }}</flux:heading>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Año Académico') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $this->academicYear->year }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha de Inicio') }}</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                {{ $this->academicYear->start_date?->translatedFormat('d M Y') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha de Fin') }}</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                {{ $this->academicYear->end_date?->translatedFormat('d M Y') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Año Actual') }}</p>
                            <div class="mt-1">
                                @if($this->academicYear->is_current)
                                    <x-ui.badge variant="success" icon="star">{{ __('Sí') }}</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral">{{ __('No') }}</x-ui.badge>
                                @endif
                            </div>
                        </div>

                        @if($this->academicYear->created_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Creado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $this->academicYear->created_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if($this->academicYear->updated_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $this->academicYear->updated_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if($this->academicYear->deleted_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Eliminado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $this->academicYear->deleted_at->translatedFormat('d M Y') }}
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
                        @can('update', $this->academicYear)
                            @if($this->academicYear->trashed())
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)"
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('common.actions.restore') }}
                                </flux:button>
                            @else
                                <flux:button 
                                    wire:click="toggleCurrent"
                                    variant="{{ $this->academicYear->is_current ? 'danger' : 'primary' }}"
                                    icon="{{ $this->academicYear->is_current ? 'x-circle' : 'star' }}"
                                    class="w-full"
                                >
                                    {{ $this->academicYear->is_current ? __('Desmarcar como Actual') : __('Marcar como Actual') }}
                                </flux:button>
                            @endif
                        @endcan

                        @can('delete', $this->academicYear)
                            @if($this->academicYear->trashed())
                                @can('forceDelete', $this->academicYear)
                                    <flux:button 
                                        wire:click="$set('showForceDeleteModal', true)"
                                        variant="danger"
                                        icon="trash"
                                        class="w-full"
                                        :disabled="!$this->canDelete()"
                                        :title="!$this->canDelete() ? __('No se puede eliminar porque tiene relaciones activas') : ''"
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
                                    :title="!$this->canDelete() ? __('No se puede eliminar porque tiene relaciones activas') : ''"
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

    {{-- Delete Modal --}}
    <flux:modal wire:model.self="showDeleteModal" name="delete-academic-year">
        <form wire:submit="delete" class="space-y-4">
            <flux:heading size="lg">{{ __('Eliminar Año Académico') }}</flux:heading>

            <flux:text class="mt-4">
                {{ __('¿Estás seguro de que deseas eliminar este año académico?') }}
                @if($this->hasRelationships())
                    <br><br>
                    <span class="text-red-600 dark:text-red-400 font-medium">
                        {{ __('No se puede eliminar este año académico porque tiene relaciones activas (convocatorias, noticias o documentos).') }}
                    </span>
                @endif
            </flux:text>

            @if(!$this->hasRelationships())
                <flux:callout variant="info" class="mt-4">
                    <flux:callout.text>
                        {{ __('Esta acción realizará una eliminación temporal (SoftDelete). El año académico se marcará como eliminado pero permanecerá en la base de datos y podrás restaurarlo más tarde desde la lista de eliminados.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">
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

    {{-- Restore Modal --}}
    <flux:modal wire:model.self="showRestoreModal" name="restore-academic-year">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading size="lg">{{ __('Restaurar Año Académico') }}</flux:heading>

            <flux:text class="mt-4">
                {{ __('¿Estás seguro de que deseas restaurar este año académico?') }}
            </flux:text>

            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="$set('showRestoreModal', false)" variant="ghost">
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

    {{-- Force Delete Modal --}}
    <flux:modal wire:model.self="showForceDeleteModal" name="force-delete-academic-year">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading size="lg">{{ __('Eliminar Permanentemente') }}</flux:heading>

            <flux:text class="mt-4">
                {{ __('¿Estás seguro de que deseas eliminar permanentemente este año académico?') }}
            </flux:text>

            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). El año académico se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>

            @if($this->academicYear->calls()->exists() || $this->academicYear->newsPosts()->exists() || $this->academicYear->documents()->exists())
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar permanentemente este año académico porque tiene relaciones activas (convocatorias, noticias o documentos). Primero debes eliminar o reasignar estas relaciones.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="danger"
                    :disabled="$this->academicYear->calls()->exists() || $this->academicYear->newsPosts()->exists() || $this->academicYear->documents()->exists()"
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
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="academic-year-updated" variant="success" />
    <x-ui.toast event="academic-year-deleted" variant="success" />
    <x-ui.toast event="academic-year-restored" variant="success" />
    <x-ui.toast event="academic-year-force-deleted" variant="warning" />
    <x-ui.toast event="academic-year-force-delete-error" variant="error" />
    <x-ui.toast event="academic-year-delete-error" variant="error" />
</div>
