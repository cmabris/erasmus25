<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $this->documentCategory->name }}
                    </h1>
                    @if($this->documentCategory->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @endif
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $this->documentCategory->slug }}</code>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$this->documentCategory->trashed())
                    @can('update', $this->documentCategory)
                        <flux:button 
                            href="{{ route('admin.document-categories.edit', $this->documentCategory) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endcan
                @endif
                <flux:button 
                    href="{{ route('admin.document-categories.index') }}" 
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
                ['label' => __('Categorías de Documentos'), 'href' => route('admin.document-categories.index'), 'icon' => 'folder'],
                ['label' => $this->documentCategory->name, 'icon' => 'folder'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Description --}}
            @if($this->documentCategory->description)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Descripción') }}</flux:heading>
                        <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $this->documentCategory->description }}</p>
                    </div>
                </x-ui.card>
            @endif

            {{-- Statistics --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Estadísticas') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-1">
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                    <flux:icon name="document" class="[:where(&)]:size-5 text-blue-600 dark:text-blue-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Documentos Asociados') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_documents'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Related Documents --}}
            @if($this->documentCategory->documents->isNotEmpty())
                <x-ui.card>
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="md">{{ __('Documentos Asociados') }}</flux:heading>
                            @if(\Illuminate\Support\Facades\Route::has('admin.documents.index'))
                                <flux:button 
                                    href="{{ route('admin.documents.index', ['categoria' => $this->documentCategory->id]) }}" 
                                    variant="ghost" 
                                    size="sm"
                                    wire:navigate
                                >
                                    {{ __('Ver todos') }}
                                </flux:button>
                            @endif
                        </div>
                        <div class="space-y-3">
                            @foreach($this->documentCategory->documents as $document)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            @if(\Illuminate\Support\Facades\Route::has('admin.documents.show'))
                                                <a href="{{ route('admin.documents.show', $document) }}" wire:navigate class="font-medium text-zinc-900 dark:text-white hover:text-erasmus-600 dark:hover:text-erasmus-400">
                                                    {{ $document->title }}
                                                </a>
                                            @else
                                                <p class="font-medium text-zinc-900 dark:text-white">{{ $document->title }}</p>
                                            @endif
                                            @if($document->description)
                                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                                    {{ $document->description }}
                                                </p>
                                            @endif
                                            <div class="mt-2 flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                                                @if($document->document_type)
                                                    <x-ui.badge variant="neutral" size="sm">
                                                        {{ ucfirst($document->document_type) }}
                                                    </x-ui.badge>
                                                @endif
                                                @if($document->created_at)
                                                    <span>{{ $document->created_at->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($document->is_active)
                                            <x-ui.badge variant="success" size="sm" class="ml-4">
                                                {{ __('Activo') }}
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral" size="sm" class="ml-4">
                                                {{ __('Inactivo') }}
                                            </x-ui.badge>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($this->statistics['total_documents'] > 10)
                            <div class="mt-4 text-center">
                                <flux:button 
                                    href="{{ route('admin.document-categories.index') }}" 
                                    variant="ghost" 
                                    size="sm"
                                    wire:navigate
                                >
                                    {{ __('Ver todos los :count documentos', ['count' => $this->statistics['total_documents']]) }}
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @else
                <x-ui.card>
                    <div class="text-center py-8">
                        <flux:icon name="document" class="[:where(&)]:size-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-4" variant="outline" />
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ __('No hay documentos asociados a esta categoría') }}
                        </p>
                    </div>
                </x-ui.card>
            @endif
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
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Nombre') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $this->documentCategory->name }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Slug') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $this->documentCategory->slug }}</code>
                            </p>
                        </div>
                        @if($this->documentCategory->order !== null)
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Orden') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $this->documentCategory->order }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $this->documentCategory->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $this->documentCategory->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($this->documentCategory->trashed())
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Eliminado') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $this->documentCategory->deleted_at->format('d/m/Y H:i') }}</p>
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
                        @if(!$this->documentCategory->trashed())
                            @can('update', $this->documentCategory)
                                <flux:button 
                                    href="{{ route('admin.document-categories.edit', $this->documentCategory) }}" 
                                    variant="primary"
                                    wire:navigate
                                    icon="pencil"
                                    class="w-full"
                                >
                                    {{ __('common.actions.edit') }}
                                </flux:button>
                            @endcan

                            @can('delete', $this->documentCategory)
                                <flux:button 
                                    wire:click="$set('showDeleteModal', true)" 
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                    :disabled="!$this->canDelete()"
                                    :tooltip="!$this->canDelete() ? __('No se puede eliminar la categoría porque tiene documentos asociados.') : __('Eliminar categoría')"
                                >
                                    {{ __('common.actions.delete') }}
                                </flux:button>
                            @endcan
                        @else
                            @can('restore', $this->documentCategory)
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)" 
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('Restaurar') }}
                                </flux:button>
                            @endcan

                            @can('forceDelete', $this->documentCategory)
                                <flux:button 
                                    wire:click="$set('showForceDeleteModal', true)" 
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                    :disabled="$this->hasRelationships"
                                    :tooltip="$this->hasRelationships ? __('No se puede eliminar permanentemente la categoría porque tiene documentos asociados.') : __('Eliminar permanentemente')"
                                >
                                    {{ __('Eliminar permanentemente') }}
                                </flux:button>
                            @endcan
                        @endif

                        <flux:button 
                            href="{{ route('admin.document-categories.index') }}" 
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
    <flux:modal name="delete-document-category" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Categoría') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta categoría?') }}
                <br>
                <strong>{{ $this->documentCategory->name }}</strong>
            </flux:text>
            @if($this->hasRelationships)
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar esta categoría porque tiene documentos asociados.') }}
                    </flux:callout.text>
                </flux:callout>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará la categoría como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
                </flux:text>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if(!$this->hasRelationships)
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
    <flux:modal name="restore-document-category" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Categoría') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar esta categoría?') }}
                <br>
                <strong>{{ $this->documentCategory->name }}</strong>
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
                        {{ __('Restaurar') }}
                    </span>
                    <span wire:loading wire:target="restore">
                        {{ __('Restaurando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Confirmation Modal --}}
    <flux:modal name="force-delete-document-category" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente esta categoría?') }}
                <br>
                <strong>{{ $this->documentCategory->name }}</strong>
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). La categoría se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($this->hasRelationships)
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar permanentemente esta categoría porque tiene documentos asociados. Primero debes eliminar o reasignar estas relaciones.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="danger"
                    :disabled="$this->hasRelationships"
                    wire:loading.attr="disabled"
                    wire:target="forceDelete"
                >
                    <span wire:loading.remove wire:target="forceDelete">
                        {{ __('Eliminar permanentemente') }}
                    </span>
                    <span wire:loading wire:target="forceDelete">
                        {{ __('Eliminando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="document-category-deleted" variant="success" />
    <x-ui.toast event="document-category-restored" variant="success" />
    <x-ui.toast event="document-category-force-deleted" variant="warning" />
    <x-ui.toast event="document-category-force-delete-error" variant="error" />
    <x-ui.toast event="document-category-delete-error" variant="error" />
</div>
