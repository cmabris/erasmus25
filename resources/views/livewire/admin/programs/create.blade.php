<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Crear Programa') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Añade un nuevo programa Erasmus+ al sistema') }}
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.programs.index') }}" 
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
                ['label' => __('common.nav.programs'), 'href' => route('admin.programs.index'), 'icon' => 'academic-cap'],
                ['label' => __('Crear'), 'icon' => 'plus'],
            ]"
        />
    </div>

    {{-- Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <form wire:submit="store" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Main Form Fields --}}
                <div class="lg:col-span-2 space-y-6">
                    <x-ui.card>
                        <div class="space-y-6">
                            {{-- Code Field --}}
                            <flux:field>
                                <flux:label>{{ __('Código') }} <span class="text-red-500">*</span></flux:label>
                                <flux:input 
                                    wire:model.live.blur="code" 
                                    placeholder="Ej: ERASM+"
                                    required
                                    autofocus
                                />
                                <flux:description>{{ __('Código único del programa (ej: ERASM+, KA1, KA2)') }}</flux:description>
                                @error('code')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Name Field --}}
                            <flux:field>
                                <flux:label>{{ __('Nombre') }} <span class="text-red-500">*</span></flux:label>
                                <flux:input 
                                    wire:model.live.blur="name" 
                                    placeholder="Ej: Erasmus+ Movilidad"
                                    required
                                />
                                <flux:description>{{ __('Nombre completo del programa') }}</flux:description>
                                @error('name')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Slug Field --}}
                            <flux:field>
                                <flux:label>{{ __('Slug') }}</flux:label>
                                <flux:input 
                                    wire:model.live.blur="slug" 
                                    placeholder="Se genera automáticamente"
                                />
                                <flux:description>{{ __('URL amigable (se genera automáticamente desde el nombre)') }}</flux:description>
                                @error('slug')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Description Field --}}
                            <flux:field>
                                <flux:label>{{ __('Descripción') }}</flux:label>
                                <flux:textarea 
                                    wire:model.live.blur="description" 
                                    placeholder="Descripción del programa..."
                                    rows="6"
                                />
                                <flux:description>{{ __('Descripción detallada del programa') }}</flux:description>
                                @error('description')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Image Upload Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Imagen del Programa') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Sube una imagen representativa del programa (opcional)') }}
                                </flux:text>
                            </div>

                            {{-- Image Preview --}}
                            @if($imagePreview)
                                <div class="relative">
                                    <img 
                                        src="{{ $imagePreview }}" 
                                        alt="{{ __('Vista previa') }}"
                                        class="h-48 w-full rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                    />
                                    <button 
                                        type="button"
                                        wire:click="removeImage"
                                        class="absolute top-2 right-2 rounded-full bg-red-500 p-2 text-white shadow-lg transition-colors hover:bg-red-600"
                                        aria-label="{{ __('Eliminar imagen') }}"
                                    >
                                        <flux:icon name="x-mark" class="[:where(&)]:size-4" variant="solid" />
                                    </button>
                                </div>
                            @endif

                            {{-- File Input --}}
                            <flux:field>
                                <flux:label>{{ __('Seleccionar imagen') }}</flux:label>
                                <input 
                                    type="file" 
                                    wire:model="image"
                                    accept="image/jpeg,image/png,image/webp,image/gif"
                                    class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-erasmus-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-erasmus-700 hover:file:bg-erasmus-100 dark:file:bg-erasmus-900/30 dark:file:text-erasmus-300"
                                />
                                <flux:description>
                                    {{ __('Formatos aceptados: JPEG, PNG, WebP, GIF. Tamaño máximo: 5MB') }}
                                </flux:description>
                                @error('image')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                                
                                {{-- Upload Progress --}}
                                <div wire:loading wire:target="image" class="mt-2">
                                    <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                        <flux:icon name="arrow-path" class="[:where(&)]:size-4 animate-spin" variant="outline" />
                                        <span>{{ __('Subiendo imagen...') }}</span>
                                    </div>
                                </div>
                            </flux:field>
                        </div>
                    </x-ui.card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Settings Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Configuración') }}</flux:heading>
                            </div>

                            {{-- Order Field --}}
                            <flux:field>
                                <flux:label>{{ __('Orden') }}</flux:label>
                                <flux:input 
                                    wire:model.live.blur="order" 
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                />
                                <flux:description>{{ __('Orden de visualización (menor número = primero)') }}</flux:description>
                                @error('order')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Active Toggle --}}
                            <flux:field>
                                <flux:checkbox wire:model.live="is_active">
                                    {{ __('Programa activo') }}
                                </flux:checkbox>
                                <flux:description>{{ __('Los programas inactivos no se mostrarán en el área pública') }}</flux:description>
                            </flux:field>
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
                                    wire:target="store"
                                >
                                    <span wire:loading.remove wire:target="store">
                                        {{ __('common.actions.save') }}
                                    </span>
                                    <span wire:loading wire:target="store">
                                        {{ __('Guardando...') }}
                                    </span>
                                </flux:button>

                                <flux:button 
                                    type="button"
                                    href="{{ route('admin.programs.index') }}" 
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
    <x-ui.toast event="program-created" variant="success" />
</div>
