<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $this->newsTag->name }}
                    </h1>
                    @if($this->newsTag->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @endif
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $this->newsTag->slug }}</code>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$this->newsTag->trashed())
                    @can('update', $this->newsTag)
                        <flux:button 
                            href="{{ route('admin.news-tags.edit', $this->newsTag) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endcan
                @endif
                <flux:button 
                    href="{{ route('admin.news-tags.index') }}" 
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
                ['label' => __('common.nav.news_tags'), 'href' => route('admin.news-tags.index'), 'icon' => 'tag'],
                ['label' => $this->newsTag->name, 'icon' => 'tag'],
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
                    <div class="grid gap-4 sm:grid-cols-1">
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                    <flux:icon name="newspaper" class="[:where(&)]:size-5 text-blue-600 dark:text-blue-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Noticias Asociadas') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_news'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Related News Posts --}}
            @if($this->newsTag->newsPosts->isNotEmpty())
                <x-ui.card>
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="md">{{ __('Noticias Asociadas') }}</flux:heading>
                            @if(\Illuminate\Support\Facades\Route::has('admin.news.index'))
                                <flux:button 
                                    href="{{ route('admin.news.index', ['etiqueta' => $this->newsTag->id]) }}" 
                                    variant="ghost" 
                                    size="sm"
                                    wire:navigate
                                >
                                    {{ __('Ver todas') }}
                                </flux:button>
                            @endif
                        </div>
                        <div class="space-y-3">
                            @foreach($this->newsTag->newsPosts as $newsPost)
                                @php
                                    $statusConfig = match($newsPost->status) {
                                        'publicado' => ['variant' => 'success', 'key' => 'published'],
                                        'en_revision' => ['variant' => 'warning', 'key' => 'review'],
                                        'archivado' => ['variant' => 'neutral', 'key' => 'archived'],
                                        default => ['variant' => 'neutral', 'key' => 'draft'],
                                    };
                                @endphp
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            @if(\Illuminate\Support\Facades\Route::has('admin.news.show'))
                                                <a href="{{ route('admin.news.show', $newsPost) }}" wire:navigate class="font-medium text-zinc-900 dark:text-white hover:text-erasmus-600 dark:hover:text-erasmus-400">
                                                    {{ $newsPost->title }}
                                                </a>
                                            @else
                                                <p class="font-medium text-zinc-900 dark:text-white">{{ $newsPost->title }}</p>
                                            @endif
                                            @if($newsPost->excerpt)
                                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                                    {{ $newsPost->excerpt }}
                                                </p>
                                            @endif
                                            <div class="mt-2 flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                                                @if($newsPost->author)
                                                    <span>{{ __('Por') }}: {{ $newsPost->author->name }}</span>
                                                @endif
                                                @if($newsPost->published_at)
                                                    <span>{{ $newsPost->published_at->format('d/m/Y') }}</span>
                                                @else
                                                    <span>{{ $newsPost->created_at->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <x-ui.badge variant="{{ $statusConfig['variant'] }}" size="sm" class="ml-4">
                                            {{ __('common.news_status.' . $statusConfig['key']) }}
                                        </x-ui.badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($this->statistics['total_news'] > 10)
                            <div class="mt-4 text-center">
                                <flux:button 
                                    href="{{ route('admin.news-tags.index') }}" 
                                    variant="ghost" 
                                    size="sm"
                                    wire:navigate
                                >
                                    {{ __('Ver todas las :count noticias', ['count' => $this->statistics['total_news']]) }}
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @else
                <x-ui.card>
                    <div class="text-center py-8">
                        <flux:icon name="newspaper" class="[:where(&)]:size-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-4" variant="outline" />
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ __('No hay noticias asociadas a esta etiqueta') }}
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
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $this->newsTag->name }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Slug') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $this->newsTag->slug }}</code>
                            </p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $this->newsTag->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $this->newsTag->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($this->newsTag->trashed())
                            <div>
                                <p class="text-zinc-600 dark:text-zinc-400">{{ __('Eliminado') }}</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $this->newsTag->deleted_at->format('d/m/Y H:i') }}</p>
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
                        @if(!$this->newsTag->trashed())
                            @can('update', $this->newsTag)
                                <flux:button 
                                    href="{{ route('admin.news-tags.edit', $this->newsTag) }}" 
                                    variant="primary"
                                    wire:navigate
                                    icon="pencil"
                                    class="w-full"
                                >
                                    {{ __('common.actions.edit') }}
                                </flux:button>
                            @endcan

                            @can('delete', $this->newsTag)
                                <flux:button 
                                    wire:click="$set('showDeleteModal', true)" 
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                    :disabled="!$this->canDelete()"
                                    :tooltip="!$this->canDelete() ? __('No se puede eliminar la etiqueta porque tiene noticias asociadas.') : __('Eliminar etiqueta')"
                                >
                                    {{ __('common.actions.delete') }}
                                </flux:button>
                            @endcan
                        @else
                            @can('restore', $this->newsTag)
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)" 
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('Restaurar') }}
                                </flux:button>
                            @endcan

                            @can('forceDelete', $this->newsTag)
                                <flux:button 
                                    wire:click="$set('showForceDeleteModal', true)" 
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                    :disabled="$this->hasRelationships"
                                    :tooltip="$this->hasRelationships ? __('No se puede eliminar permanentemente la etiqueta porque tiene noticias asociadas.') : __('Eliminar permanentemente')"
                                >
                                    {{ __('Eliminar permanentemente') }}
                                </flux:button>
                            @endcan
                        @endif

                        <flux:button 
                            href="{{ route('admin.news-tags.index') }}" 
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
    <flux:modal name="delete-news-tag" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Etiqueta') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta etiqueta?') }}
                <br>
                <strong>{{ $this->newsTag->name }}</strong>
            </flux:text>
            @if($this->hasRelationships)
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar esta etiqueta porque tiene noticias asociadas.') }}
                    </flux:callout.text>
                </flux:callout>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará la etiqueta como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
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
    <flux:modal name="restore-news-tag" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Etiqueta') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar esta etiqueta?') }}
                <br>
                <strong>{{ $this->newsTag->name }}</strong>
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
    <flux:modal name="force-delete-news-tag" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente esta etiqueta?') }}
                <br>
                <strong>{{ $this->newsTag->name }}</strong>
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). La etiqueta se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            @if($this->hasRelationships)
                <flux:callout variant="warning" class="mt-4">
                    <flux:callout.text>
                        {{ __('No se puede eliminar permanentemente esta etiqueta porque tiene noticias asociadas. Primero debes eliminar o reasignar estas relaciones.') }}
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
    <x-ui.toast event="news-tag-deleted" variant="success" />
    <x-ui.toast event="news-tag-restored" variant="success" />
    <x-ui.toast event="news-tag-force-deleted" variant="warning" />
    <x-ui.toast event="news-tag-force-delete-error" variant="error" />
    <x-ui.toast event="news-tag-delete-error" variant="error" />
</div>
