<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Categoría') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información de la categoría') }}: <strong>{{ $documentCategory->name }}</strong>
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.document-categories.index') }}" 
                variant="ghost"
                wire:navigate
                icon="arrow-left"
            >
                {{ __('common.actions.back') }}
            </flux:button>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Categorías de Documentos'), 'href' => route('admin.document-categories.index'), 'icon' => 'folder'],
                ['label' => $documentCategory->name, 'icon' => 'folder'],
                ['label' => __('common.actions.edit'), 'icon' => 'pencil'],
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
                            {{-- Name Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Nombre') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('El nombre de la categoría que se mostrará en los documentos.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="name" 
                                    placeholder="Ej: Convocatorias, Modelos, Seguros, etc."
                                    required
                                    autofocus
                                    maxlength="255"
                                />
                                <flux:description>{{ __('El nombre de la categoría que se mostrará públicamente') }}</flux:description>
                                @error('name')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Slug Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Slug') }}
                                    <flux:tooltip content="{{ __('El slug se genera automáticamente desde el nombre, pero puedes editarlo manualmente si lo deseas. Debe ser único y solo contener letras minúsculas, números y guiones.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="slug" 
                                    placeholder="Se genera automáticamente"
                                    maxlength="255"
                                />
                                <flux:description>{{ __('URL amigable para la categoría (se genera automáticamente desde el nombre)') }}</flux:description>
                                @error('slug')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Description Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Descripción') }}
                                    <flux:tooltip content="{{ __('Una descripción opcional de la categoría que ayudará a entender su propósito.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:textarea 
                                    wire:model.blur="description" 
                                    placeholder="Ej: Documentos relacionados con las convocatorias de movilidad..."
                                    rows="4"
                                />
                                <flux:description>{{ __('Descripción opcional de la categoría') }}</flux:description>
                                @error('description')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Relations Info Card --}}
                    @php
                        $documentsCount = $documentCategory->documents()->count();
                    @endphp
                    @if($documentsCount > 0)
                        <x-ui.card>
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="sm">{{ __('Información de Relaciones') }}</flux:heading>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Documentos asociados') }}:</span>
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $documentsCount }}</span>
                                    </div>
                                    @if($documentsCount > 0)
                                        <flux:callout variant="info" class="mt-4">
                                            <flux:callout.text>
                                                {{ __('Esta categoría está asociada a :count documento(s). Si cambias el nombre o el slug, los documentos existentes seguirán usando esta categoría.', ['count' => $documentsCount]) }}
                                            </flux:callout.text>
                                        </flux:callout>
                                    @endif
                                </div>
                            </div>
                        </x-ui.card>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Order Field --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Orden') }}</flux:heading>
                            </div>

                            <flux:field>
                                <flux:label>
                                    {{ __('Orden de visualización') }}
                                    <flux:tooltip content="{{ __('El orden en el que se mostrará esta categoría en los listados. Las categorías con menor número aparecerán primero.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    type="number"
                                    wire:model.blur="order" 
                                    placeholder="0"
                                    min="0"
                                    step="1"
                                />
                                <flux:description>{{ __('Número para ordenar la categoría (opcional)') }}</flux:description>
                                @error('order')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Info Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $documentCategory->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $documentCategory->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Documentos') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $documentsCount }}</span>
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
                                    href="{{ route('admin.document-categories.index') }}" 
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
    <x-ui.toast event="document-category-updated" variant="success" />
</div>
