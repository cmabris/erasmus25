<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $document->title }}
                    </h1>
                    @if($document->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @elseif($document->is_active)
                        <x-ui.badge variant="success" size="lg">
                            {{ __('Activo') }}
                        </x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral" size="lg">
                            {{ __('Inactivo') }}
                        </x-ui.badge>
                    @endif
                    <x-ui.badge :variant="$this->getDocumentTypeColor($document->document_type)" size="lg">
                        {{ $this->getDocumentTypeOptions()[$document->document_type] ?? $document->document_type }}
                    </x-ui.badge>
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($document->category)
                        {{ __('Categoría') }}: <strong>{{ $document->category->name }}</strong>
                    @endif
                    @if($document->program)
                        · {{ __('Programa') }}: <strong>{{ $document->program->name }}</strong>
                    @endif
                    @if($document->academicYear)
                        · {{ __('Año Académico') }}: <strong>{{ $document->academicYear->year }}</strong>
                    @endif
                    @if($document->version)
                        · {{ __('Versión') }}: <strong>{{ $document->version }}</strong>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$document->trashed())
                    @can('update', $document)
                        <flux:button 
                            href="{{ route('admin.documents.edit', $document) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endcan
                @endif
                <flux:button 
                    href="{{ route('admin.documents.index') }}" 
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
                ['label' => __('Documentos'), 'href' => route('admin.documents.index'), 'icon' => 'document'],
                ['label' => $document->title, 'icon' => 'document'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Document Information --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Información del Documento') }}</flux:heading>
                    <div class="space-y-4">
                        @if($document->description)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Descripción') }}</p>
                                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $document->description }}</p>
                            </div>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Categoría') }}</p>
                                <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $document->category->name ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tipo') }}</p>
                                <p class="mt-1">
                                    <x-ui.badge :variant="$this->getDocumentTypeColor($document->document_type)" size="sm">
                                        {{ $this->getDocumentTypeOptions()[$document->document_type] ?? $document->document_type }}
                                    </x-ui.badge>
                                </p>
                            </div>
                            @if($document->program)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Programa') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $document->program->name }}
                                    </p>
                                </div>
                            @endif
                            @if($document->academicYear)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Año Académico') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $document->academicYear->year }}
                                    </p>
                                </div>
                            @endif
                            @if($document->version)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Versión') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $document->version }}
                                    </p>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Estado') }}</p>
                                <p class="mt-1">
                                    <x-ui.badge :variant="$document->is_active ? 'success' : 'neutral'" size="sm">
                                        {{ $document->is_active ? __('Activo') : __('Inactivo') }}
                                    </x-ui.badge>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Descargas') }}</p>
                                <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ number_format($document->download_count, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- File Section --}}
            @if($this->hasFile())
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Archivo del Documento') }}</flux:heading>
                        <div class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            @if(str_starts_with($this->existingFile->mime_type, 'image/'))
                                <img 
                                    src="{{ $this->existingFile->getUrl() }}" 
                                    alt="{{ $document->title }}"
                                    class="h-24 w-24 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                    loading="lazy"
                                />
                            @else
                                <flux:icon name="document" class="[:where(&)]:size-12 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                            @endif
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->existingFile->file_name }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ number_format($this->existingFile->size / 1024, 2) }} KB
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $this->existingFile->mime_type }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <flux:button 
                                    variant="primary"
                                    size="sm"
                                    href="{{ $this->existingFile->getUrl() }}"
                                    target="_blank"
                                    icon="eye"
                                >
                                    {{ __('Ver') }}
                                </flux:button>
                                <flux:button 
                                    variant="ghost"
                                    size="sm"
                                    href="{{ $this->existingFile->getUrl() }}"
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
                        <flux:icon name="document" class="[:where(&)]:size-12 mx-auto text-zinc-400" variant="outline" />
                        <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No hay archivo asociado') }}</p>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Este documento no tiene un archivo asociado') }}</p>
                    </div>
                </x-ui.card>
            @endif

            {{-- Media Consents Section --}}
            @if($this->hasRelationships)
                <x-ui.card>
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="md">{{ __('Consentimientos de Medios Asociados') }}</flux:heading>
                            <flux:callout variant="info" class="!p-2">
                                <flux:callout.text class="text-xs">
                                    {{ __('Total') }}: {{ $document->media_consents_count }}
                                </flux:callout.text>
                            </flux:callout>
                        </div>
                        @if($document->mediaConsents->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($document->mediaConsents->take(10) as $consent)
                                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                    {{ $consent->person_name ?? __('Sin nombre') }}
                                                </p>
                                                @if($consent->person_email)
                                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                        {{ $consent->person_email }}
                                                    </p>
                                                @endif
                                                <div class="mt-2 flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                                                    <span>{{ __('Tipo') }}: {{ ucfirst($consent->consent_type) }}</span>
                                                    @if($consent->consent_date)
                                                        <span>{{ __('Fecha') }}: {{ $consent->consent_date->format('d/m/Y') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <x-ui.badge :variant="$consent->consent_given ? 'success' : 'danger'" size="sm">
                                                {{ $consent->consent_given ? __('Dado') : __('No dado') }}
                                            </x-ui.badge>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($document->media_consents_count > 10)
                                <div class="mt-4 text-center">
                                    <flux:callout variant="info">
                                        <flux:callout.text>
                                            {{ __('Mostrando :count de :total consentimientos', ['count' => 10, 'total' => $document->media_consents_count]) }}
                                        </flux:callout.text>
                                    </flux:callout>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('No hay consentimientos de medios asociados a este documento') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Document Details --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Slug') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $document->slug }}</code>
                            </p>
                        </div>
                        @if($document->created_at)
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $document->created_at->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        @endif
                        @if($document->creator)
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Creado por') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $document->creator->name }}
                                </p>
                            </div>
                        @endif
                        @if($document->updated_at && $document->updated_at->ne($document->created_at))
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $document->updated_at->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        @endif
                        @if($document->updater)
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado por') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $document->updater->name }}
                                </p>
                            </div>
                        @endif
                        @if($document->trashed())
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Eliminado') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $document->deleted_at->translatedFormat('d M Y H:i') }}
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
                        @if(!$document->trashed())
                            @can('update', $document)
                                <flux:button 
                                    href="{{ route('admin.documents.edit', $document) }}" 
                                    variant="primary"
                                    wire:navigate
                                    icon="pencil"
                                    class="w-full"
                                >
                                    {{ __('common.actions.edit') }}
                                </flux:button>
                            @endcan

                            @can('delete', $document)
                                <flux:button 
                                    wire:click="$set('showDeleteModal', true)" 
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                    :disabled="!$this->canDelete()"
                                    :tooltip="!$this->canDelete() ? __('No se puede eliminar el documento porque tiene consentimientos de medios asociados.') : __('Eliminar documento')"
                                >
                                    {{ __('common.actions.delete') }}
                                </flux:button>
                            @endcan
                        @else
                            @can('restore', $document)
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)" 
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('Restaurar') }}
                                </flux:button>
                            @endcan

                            @can('forceDelete', $document)
                                <flux:button 
                                    wire:click="$set('showForceDeleteModal', true)" 
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                    :disabled="$this->hasRelationships"
                                    :tooltip="$this->hasRelationships ? __('No se puede eliminar permanentemente el documento porque tiene consentimientos de medios asociados.') : __('Eliminar permanentemente')"
                                >
                                    {{ __('Eliminar permanentemente') }}
                                </flux:button>
                            @endcan
                        @endif

                        <flux:button 
                            href="{{ route('admin.documents.index') }}" 
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
    <flux:modal name="delete-document" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Documento') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar este documento?') }}
                <br>
                <strong>{{ $document->title }}</strong>
            </flux:text>
            @if($this->hasRelationships)
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar este documento porque tiene consentimientos de medios asociados.') }}
                    </flux:callout.text>
                </flux:callout>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará el documento como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.') }}
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
    <flux:modal name="restore-document" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Documento') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar este documento?') }}
                <br>
                <strong>{{ $document->title }}</strong>
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
    <flux:modal name="force-delete-document" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente este documento?') }}
                <br>
                <strong>{{ $document->title }}</strong>
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). El documento se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($this->hasRelationships)
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar permanentemente este documento porque tiene consentimientos de medios asociados. Primero debes eliminar o reasignar estas relaciones.') }}
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
    <x-ui.toast event="document-deleted" variant="success" />
    <x-ui.toast event="document-restored" variant="success" />
    <x-ui.toast event="document-force-deleted" variant="warning" />
    <x-ui.toast event="document-force-delete-error" variant="error" />
    <x-ui.toast event="document-delete-error" variant="error" />
</div>
