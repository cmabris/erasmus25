<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Etiqueta') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información de la etiqueta') }}: <strong>{{ $newsTag->name }}</strong>
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.news-tags.index') }}" 
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
                ['label' => __('common.nav.news_tags'), 'href' => route('admin.news-tags.index'), 'icon' => 'tag'],
                ['label' => $newsTag->name, 'icon' => 'tag'],
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
                                    <flux:tooltip content="{{ __('El nombre de la etiqueta que se mostrará en las noticias.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="name" 
                                    placeholder="Ej: Movilidad, Experiencias, etc."
                                    required
                                    autofocus
                                    maxlength="255"
                                />
                                <flux:description>{{ __('El nombre de la etiqueta que se mostrará públicamente') }}</flux:description>
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
                                <flux:description>{{ __('URL amigable para la etiqueta (se genera automáticamente desde el nombre)') }}</flux:description>
                                @error('slug')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Relations Info Card --}}
                    @php
                        $newsPostsCount = $newsTag->newsPosts()->count();
                    @endphp
                    @if($newsPostsCount > 0)
                        <x-ui.card>
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="sm">{{ __('Información de Relaciones') }}</flux:heading>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Noticias asociadas') }}:</span>
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $newsPostsCount }}</span>
                                    </div>
                                    @if($newsPostsCount > 0)
                                        <flux:callout variant="info" class="mt-4">
                                            <flux:callout.text>
                                                {{ __('Esta etiqueta está asociada a :count noticia(s). Si cambias el nombre o el slug, las noticias existentes seguirán usando esta etiqueta.', ['count' => $newsPostsCount]) }}
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
                    {{-- Info Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $newsTag->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $newsTag->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Noticias') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $newsPostsCount }}</span>
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
                                    href="{{ route('admin.news-tags.index') }}" 
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
    <x-ui.toast event="news-tag-updated" variant="success" />
</div>
