<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Traducción') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica el valor de la traducción') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('view', $translation)
                    <flux:button 
                        href="{{ route('admin.translations.show', $translation) }}" 
                        variant="ghost"
                        wire:navigate
                        icon="eye"
                    >
                        {{ __('Ver') }}
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
                ['label' => __('Editar'), 'icon' => 'pencil'],
            ]"
        />
    </div>

    {{-- Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <form wire:submit="update" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Main Form Fields --}}
                <div class="lg:col-span-2 space-y-6">
                    <x-ui.card>
                        <div class="space-y-6">
                            {{-- Read-only Information --}}
                            <div class="space-y-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <div>
                                    <flux:heading size="sm">{{ __('Información de la Traducción') }}</flux:heading>
                                    <flux:description class="mt-1">
                                        {{ __('Esta información no se puede modificar. Solo puedes editar el valor de la traducción.') }}
                                    </flux:description>
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    {{-- Model Type --}}
                                    <div>
                                        <flux:field>
                                            <flux:label>{{ __('Modelo') }}</flux:label>
                                            <div class="mt-1">
                                                <flux:badge variant="info">
                                                    {{ $this->getModelTypeDisplayName($translation->translatable_type) }}
                                                </flux:badge>
                                            </div>
                                        </flux:field>
                                    </div>

                                    {{-- Language --}}
                                    <div>
                                        <flux:field>
                                            <flux:label>{{ __('Idioma') }}</flux:label>
                                            <div class="mt-1 flex items-center gap-2">
                                                <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                                    {{ $translation->language->name ?? '-' }}
                                                </span>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    ({{ $translation->language->code ?? '-' }})
                                                </span>
                                            </div>
                                        </flux:field>
                                    </div>

                                    {{-- Translatable Record --}}
                                    <div>
                                        <flux:field>
                                            <flux:label>{{ __('Registro') }}</flux:label>
                                            <div class="mt-1 text-sm text-zinc-900 dark:text-white">
                                                {{ $this->getTranslatableDisplayName() }}
                                            </div>
                                        </flux:field>
                                    </div>

                                    {{-- Field --}}
                                    <div>
                                        <flux:field>
                                            <flux:label>{{ __('Campo') }}</flux:label>
                                            <div class="mt-1">
                                                <code class="rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200">
                                                    {{ $translation->field }}
                                                </code>
                                            </div>
                                        </flux:field>
                                    </div>
                                </div>
                            </div>

                            {{-- Value Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Valor de la Traducción') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('Modifica el texto traducido para el campo seleccionado') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:textarea 
                                    wire:model.blur="value" 
                                    placeholder="{{ __('Introduce el texto traducido...') }}"
                                    required
                                    rows="8"
                                    autofocus
                                />
                                <flux:description>
                                    {{ __('Texto traducido. Puedes usar múltiples líneas si es necesario.') }}
                                    @if($value)
                                        <span class="block mt-1 text-xs">
                                            {{ __('Caracteres: :count', ['count' => strlen($value)]) }}
                                        </span>
                                    @endif
                                </flux:description>
                                @error('value')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
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
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $translation->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $translation->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Idioma') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">
                                        {{ $translation->language->name ?? '-' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Campo') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">
                                        <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-700">{{ $translation->field }}</code>
                                    </span>
                                </div>
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
                                <flux:button 
                                    type="submit" 
                                    variant="primary"
                                    icon="check"
                                    class="w-full"
                                    wire:loading.attr="disabled"
                                    wire:target="update"
                                >
                                    <span wire:loading.remove wire:target="update">
                                        {{ __('common.actions.save') }}
                                    </span>
                                    <span wire:loading wire:target="update">
                                        {{ __('Guardando...') }}
                                    </span>
                                </flux:button>

                                <flux:button 
                                    type="button"
                                    href="{{ route('admin.translations.index') }}" 
                                    variant="ghost"
                                    wire:navigate
                                    class="w-full"
                                >
                                    {{ __('common.actions.cancel') }}
                                </flux:button>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </form>
    </div>

    {{-- Toast Notifications --}}
    <x-ui.toast event="translation-updated" variant="success" />
</div>
