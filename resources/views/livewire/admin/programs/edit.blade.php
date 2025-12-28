<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Programa') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información del programa') }}: <strong>{{ $program->name }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <flux:button 
                    href="{{ route('admin.programs.show', $program) }}" 
                    variant="ghost"
                    wire:navigate
                    icon="eye"
                >
                    {{ __('common.actions.view') }}
                </flux:button>
                <flux:button 
                    href="{{ route('admin.programs.index') }}" 
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
                ['label' => __('common.nav.programs'), 'href' => route('admin.programs.index'), 'icon' => 'academic-cap'],
                ['label' => $program->name, 'href' => route('admin.programs.show', $program), 'icon' => 'academic-cap'],
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
                                    {{ __('Sube una nueva imagen o mantén la actual') }}
                                </flux:text>
                            </div>

                            {{-- Current Image --}}
                            @if($this->hasExistingImage() && !$removeExistingImage && !$imagePreview)
                                <div class="relative">
                                    <img 
                                        src="{{ $this->getCurrentImageUrl() }}" 
                                        alt="{{ $program->name }}"
                                        class="h-48 w-full rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                    />
                                    <button 
                                        type="button"
                                        wire:click="toggleRemoveExistingImage"
                                        class="absolute top-2 right-2 rounded-full bg-red-500 p-2 text-white shadow-lg transition-colors hover:bg-red-600"
                                        aria-label="{{ __('Eliminar imagen') }}"
                                    >
                                        <flux:icon name="trash" class="[:where(&)]:size-4" variant="solid" />
                                    </button>
                                    <div class="mt-2 text-center">
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Imagen actual') }}
                                        </flux:text>
                                    </div>
                                </div>
                            @endif

                            {{-- New Image Preview --}}
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
                                    <div class="mt-2 text-center">
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Nueva imagen (preview)') }}
                                        </flux:text>
                                    </div>
                                </div>
                            @endif

                            {{-- Remove Existing Image Checkbox --}}
                            @if($this->hasExistingImage() && !$imagePreview)
                                <flux:field>
                                    <flux:checkbox wire:model.live="removeExistingImage">
                                        {{ __('Eliminar imagen actual') }}
                                    </flux:checkbox>
                                    <flux:description>{{ __('Marca esta opción para eliminar la imagen actual sin subir una nueva') }}</flux:description>
                                </flux:field>
                            @endif

                            {{-- File Input --}}
                            @if(!$removeExistingImage)
                                <flux:field>
                                    <flux:label>
                                        @if($this->hasExistingImage() && !$imagePreview)
                                            {{ __('Reemplazar imagen') }}
                                        @else
                                            {{ __('Seleccionar imagen') }}
                                        @endif
                                    </flux:label>
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
                            @endif
                        </div>
                    </x-ui.card>

                    {{-- Translations Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Traducciones') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Gestiona las traducciones del programa en diferentes idiomas') }}
                                </flux:text>
                            </div>

                            @foreach($this->availableLanguages as $language)
                                @php
                                    $langCode = $language->code;
                                    $translationName = $translations[$langCode]['name'] ?? '';
                                    $translationDescription = $translations[$langCode]['description'] ?? '';
                                @endphp
                                
                                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                    <div class="mb-4 flex items-center gap-2">
                                        <flux:heading size="xs">{{ $language->name }} ({{ strtoupper($langCode) }})</flux:heading>
                                        @if($langCode === getCurrentLanguageCode())
                                            <x-ui.badge color="blue" size="sm">{{ __('Idioma actual') }}</x-ui.badge>
                                        @endif
                                    </div>

                                    <div class="space-y-4">
                                        {{-- Translated Name --}}
                                        <flux:field>
                                            <flux:label>{{ __('Nombre traducido') }}</flux:label>
                                            <flux:input 
                                                wire:model.live.blur="translations.{{ $langCode }}.name" 
                                                placeholder="{{ __('Dejar vacío para usar el nombre por defecto') }}"
                                            />
                                            <flux:description>
                                                {{ __('Si está vacío, se usará el nombre en el idioma por defecto') }}
                                            </flux:description>
                                        </flux:field>

                                        {{-- Translated Description --}}
                                        <flux:field>
                                            <flux:label>{{ __('Descripción traducida') }}</flux:label>
                                            <flux:textarea 
                                                wire:model.live.blur="translations.{{ $langCode }}.description" 
                                                placeholder="{{ __('Dejar vacío para usar la descripción por defecto') }}"
                                                rows="3"
                                            />
                                            <flux:description>
                                                {{ __('Si está vacío, se usará la descripción en el idioma por defecto') }}
                                            </flux:description>
                                        </flux:field>
                                    </div>
                                </div>
                            @endforeach

                            @if($this->availableLanguages->isEmpty())
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('No hay idiomas disponibles para traducir') }}
                                </flux:text>
                            @endif
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
                                    href="{{ route('admin.programs.show', $program) }}" 
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
    <x-ui.toast event="program-updated" variant="success" />
</div>
