<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ __('Ver Traducción') }}
                    </h1>
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Detalles completos de la traducción') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $translation)
                    <flux:button 
                        href="{{ route('admin.translations.edit', $translation) }}" 
                        variant="primary"
                        wire:navigate
                        icon="pencil"
                    >
                        {{ __('common.actions.edit') }}
                    </flux:button>
                @endcan
                @can('delete', $translation)
                    <flux:button 
                        variant="ghost"
                        wire:click="confirmDelete"
                        icon="trash"
                        class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    >
                        {{ __('common.actions.delete') }}
                    </flux:button>
                @endcan
                <flux:button 
                    href="{{ route('admin.translations.index') }}" 
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
                ['label' => __('common.nav.translations'), 'href' => route('admin.translations.index'), 'icon' => 'language'],
                ['label' => __('Ver'), 'icon' => 'eye'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Translation Value --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Valor de la Traducción') }}</flux:heading>
                    <div class="prose prose-sm max-w-none dark:prose-invert">
                        <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                            {{ $translation->value }}
                        </p>
                    </div>
                </div>
            </x-ui.card>

            {{-- Translation Details --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Detalles de la Traducción') }}</flux:heading>
                    <div class="space-y-4">
                        {{-- Model Type --}}
                        <div class="flex items-start justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Modelo Traducible') }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $this->getModelTypeDisplayName($translation->translatable_type) }}
                                </p>
                            </div>
                            <div>
                                <flux:badge variant="info">
                                    {{ $this->getModelTypeDisplayName($translation->translatable_type) }}
                                </flux:badge>
                            </div>
                        </div>

                        {{-- Translatable Record --}}
                        <div class="flex items-start justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Registro') }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    @if($this->isTranslatableDeleted())
                                        <span class="italic text-zinc-500 dark:text-zinc-400">
                                            {{ $this->getTranslatableDisplayName() }}
                                        </span>
                                        <x-ui.badge variant="danger" size="sm" class="ml-2">
                                            {{ __('Eliminado') }}
                                        </x-ui.badge>
                                    @else
                                        @if($this->getTranslatableRoute())
                                            <a 
                                                href="{{ $this->getTranslatableRoute() }}" 
                                                class="text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300"
                                                wire:navigate
                                            >
                                                {{ $this->getTranslatableDisplayName() }}
                                            </a>
                                        @else
                                            {{ $this->getTranslatableDisplayName() }}
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Field --}}
                        <div class="flex items-start justify-between border-b border-zinc-200 pb-3 dark:border-zinc-700">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Campo') }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    <code class="rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200">
                                        {{ $translation->field }}
                                    </code>
                                </p>
                            </div>
                        </div>

                        {{-- Language --}}
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ __('Idioma') }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $translation->language->name ?? '-' }}
                                    @if($translation->language)
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                            ({{ $translation->language->code }})
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Info Card --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Creado:') }}</span>
                            <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                                {{ $translation->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Actualizado:') }}</span>
                            <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                                {{ $translation->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        @if($translation->created_at->diffInDays($translation->updated_at) > 0)
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Última actualización:') }}</span>
                                <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                                    {{ $translation->updated_at->diffForHumans() }}
                                </p>
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
                        @can('update', $translation)
                            <flux:button 
                                href="{{ route('admin.translations.edit', $translation) }}" 
                                variant="primary"
                                wire:navigate
                                icon="pencil"
                                class="w-full"
                            >
                                {{ __('common.actions.edit') }}
                            </flux:button>
                        @endcan
                        @can('delete', $translation)
                            <flux:button 
                                variant="ghost"
                                wire:click="confirmDelete"
                                icon="trash"
                                class="w-full text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                            >
                                {{ __('common.actions.delete') }}
                            </flux:button>
                        @endcan
                        <flux:button 
                            href="{{ route('admin.translations.index') }}" 
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
    <flux:modal name="delete-translation" wire:model="showDeleteModal">
        <form wire:submit="delete" class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Eliminar Traducción') }}</flux:heading>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('¿Estás seguro de que deseas eliminar esta traducción? Esta acción no se puede deshacer.') }}
                </p>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    {{ __('Traducción a eliminar:') }}
                </p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    <strong>{{ $this->getModelTypeDisplayName($translation->translatable_type) }}</strong> · 
                    {{ $this->getTranslatableDisplayName() }} · 
                    <code class="text-xs">{{ $translation->field }}</code> · 
                    {{ $translation->language->name ?? '-' }}
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button 
                    type="button"
                    variant="ghost"
                    wire:click="$set('showDeleteModal', false)"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
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
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="translation-deleted" variant="success" />
</div>
