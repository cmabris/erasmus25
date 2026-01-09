<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Configuración') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la configuración del sistema') }}: <strong><code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $setting->key }}</code></strong>
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.settings.index') }}" 
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
                ['label' => __('Configuración del Sistema'), 'href' => route('admin.settings.index'), 'icon' => 'cog-6-tooth'],
                ['label' => $setting->key, 'icon' => 'cog-6-tooth'],
                ['label' => __('common.actions.edit'), 'icon' => 'pencil'],
            ]"
        />
    </div>

    {{-- Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <form wire:submit="confirmUpdate" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Main Form Fields --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Read-only Information --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información de la Configuración') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Esta información no se puede modificar') }}
                                </flux:text>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <flux:label>{{ __('Clave') }}</flux:label>
                                    <div class="mt-1">
                                        <code class="text-sm bg-zinc-100 dark:bg-zinc-800 px-3 py-2 rounded block font-mono">
                                            {{ $setting->key }}
                                        </code>
                                    </div>
                                </div>
                                <div>
                                    <flux:label>{{ __('Tipo') }}</flux:label>
                                    <div class="mt-1">
                                        <x-ui.badge variant="info" size="sm">
                                            {{ $this->getTypeLabel($setting->type) }}
                                        </x-ui.badge>
                                    </div>
                                </div>
                                <div>
                                    <flux:label>{{ __('Grupo') }}</flux:label>
                                    <div class="mt-1">
                                        <x-ui.badge variant="primary" size="sm">
                                            {{ $this->getGroupLabel($setting->group) }}
                                        </x-ui.badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Value Field (Dynamic based on type) --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Valor') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('El valor de la configuración según su tipo') }}
                                </flux:text>
                            </div>

                            @if($setting->type === 'string')
                                {{-- Special handling for center_logo with file upload --}}
                                @if($this->isCenterLogo())
                                    <flux:field>
                                        <flux:label>
                                            {{ __('Logotipo del Centro') }} <span class="text-red-500">*</span>
                                        </flux:label>
                                        
                                        {{-- Current Logo Preview --}}
                                        @if($this->getCurrentLogoUrl() && !$removeExistingLogo && !$logoFile)
                                            <div class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                                <div class="mb-3 flex items-center justify-between">
                                                    <flux:heading size="xs">{{ __('Logo Actual') }}</flux:heading>
                                                    <flux:button 
                                                        type="button"
                                                        wire:click="removeLogo"
                                                        variant="ghost"
                                                        size="sm"
                                                        icon="trash"
                                                    >
                                                        {{ __('Eliminar') }}
                                                    </flux:button>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <img 
                                                        src="{{ $this->getCurrentLogoUrl() }}" 
                                                        alt="{{ __('Logo del centro') }}"
                                                        class="h-20 w-auto max-w-[200px] object-contain rounded-lg border border-zinc-200 dark:border-zinc-700"
                                                        onerror="this.style.display='none';"
                                                    />
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                            {{ __('Logo actual configurado') }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ __('Puedes subir un nuevo logo para reemplazarlo') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- File Upload with FilePond --}}
                                        @if(!$removeExistingLogo)
                                            <x-filepond::upload 
                                                wire:model="logoFile"
                                                accepted-file-types="image/jpeg,image/jpg,image/png,image/webp,image/svg+xml"
                                                max-file-size="5MB"
                                                label-idle='{{ __("Arrastra tu logo aquí o") }} <span class="filepond--label-action">{{ __("selecciona") }}</span>'
                                                label-file-type-not-allowed="{{ __('Solo se permiten imágenes (JPEG, JPG, PNG, WebP, SVG)') }}"
                                                label-file-size-too-large="{{ __('El archivo es demasiado grande (máximo 5MB)') }}"
                                                label-file-size-too-small="{{ __('El archivo es demasiado pequeño') }}"
                                                label-file-loading="{{ __('Cargando') }}"
                                                label-file-processing="{{ __('Subiendo') }}"
                                                label-file-processing-complete="{{ __('Subida completa') }}"
                                                label-file-processing-error="{{ __('Error durante la subida') }}"
                                                label-tap-to-cancel="{{ __('Toca para cancelar') }}"
                                                label-tap-to-retry="{{ __('Toca para reintentar') }}"
                                                label-tap-to-undo="{{ __('Toca para deshacer') }}"
                                            />
                                            
                                            <flux:description>
                                                {{ __('Formatos aceptados: JPEG, JPG, PNG, WebP, SVG. Tamaño máximo: 5MB. También puedes introducir una URL manualmente.') }}
                                            </flux:description>
                                        @else
                                            <flux:callout variant="info" class="mt-2">
                                                <flux:callout.text>
                                                    {{ __('El logo actual será eliminado al guardar. Puedes subir un nuevo logo o introducir una URL manualmente.') }}
                                                </flux:callout.text>
                                            </flux:callout>
                                        @endif

                                        {{-- Manual URL Input (Alternative) --}}
                                        <div class="mt-4">
                                            <flux:field>
                                                <flux:label>
                                                    {{ __('O introducir URL manualmente') }}
                                                </flux:label>
                                                <flux:input 
                                                    type="url"
                                                    wire:model.live.blur="value" 
                                                    placeholder="https://ejemplo.com/logo.jpg o /images/logo.jpg"
                                                />
                                                <flux:description>
                                                    {{ __('Si prefieres usar una URL externa o una ruta relativa, introdúcela aquí. Si subes un archivo, esta URL será reemplazada.') }}
                                                </flux:description>
                                                @error('value')
                                                    <flux:error>{{ $message }}</flux:error>
                                                @enderror
                                            </flux:field>
                                        </div>

                                        @error('logoFile')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                @else
                                    {{-- Regular string input for other settings --}}
                                    <flux:field>
                                        <flux:label>
                                            {{ __('Valor') }} <span class="text-red-500">*</span>
                                        </flux:label>
                                        <flux:textarea 
                                            wire:model.live.blur="value" 
                                            placeholder="{{ __('Ingrese el valor de texto') }}"
                                            required
                                            rows="4"
                                        />
                                        <flux:description>{{ __('Valor de texto para esta configuración') }}</flux:description>
                                        @error('value')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                @endif
                            @elseif($setting->type === 'integer')
                                <flux:field>
                                    <flux:label>
                                        {{ __('Valor') }} <span class="text-red-500">*</span>
                                    </flux:label>
                                    <flux:input 
                                        type="number"
                                        wire:model.live.blur="value" 
                                        placeholder="{{ __('Ingrese un número entero') }}"
                                        required
                                        step="1"
                                    />
                                    <flux:description>{{ __('Número entero para esta configuración') }}</flux:description>
                                    @error('value')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            @elseif($setting->type === 'boolean')
                                <flux:field>
                                    <flux:label>
                                        {{ __('Valor') }} <span class="text-red-500">*</span>
                                    </flux:label>
                                    <div class="mt-2">
                                        <flux:switch 
                                            wire:model.live="value"
                                            :label="$value ? __('Sí') : __('No')"
                                        />
                                    </div>
                                    <flux:description>{{ __('Activar o desactivar esta configuración') }}</flux:description>
                                    @error('value')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            @elseif($setting->type === 'json')
                                <flux:field>
                                    <flux:label>
                                        {{ __('Valor JSON') }} <span class="text-red-500">*</span>
                                        <flux:tooltip content="{{ __('Ingrese un JSON válido. Se validará automáticamente.') }}" position="top">
                                            <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                        </flux:tooltip>
                                    </flux:label>
                                    <flux:textarea 
                                        wire:model.live.blur="value" 
                                        placeholder='{"key": "value"}'
                                        required
                                        rows="8"
                                        class="font-mono text-sm"
                                    />
                                    <flux:description>{{ __('JSON válido para esta configuración. Se formateará automáticamente.') }}</flux:description>
                                    @error('value')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                    
                                    {{-- JSON Preview --}}
                                    @if($jsonPreview)
                                        <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                            <div class="mb-2 flex items-center justify-between">
                                                <flux:heading size="xs">{{ __('Vista Previa JSON') }}</flux:heading>
                                                <x-ui.badge variant="success" size="sm">{{ __('Válido') }}</x-ui.badge>
                                            </div>
                                            <pre class="overflow-x-auto text-xs text-zinc-700 dark:text-zinc-300"><code>{{ $jsonPreview }}</code></pre>
                                        </div>
                                    @elseif($value && $setting->type === 'json')
                                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                                            <div class="flex items-center gap-2">
                                                <flux:icon name="exclamation-triangle" class="[:where(&)]:size-5 text-red-600 dark:text-red-400" variant="outline" />
                                                <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ __('JSON inválido') }}</span>
                                            </div>
                                            <p class="mt-2 text-xs text-red-600 dark:text-red-400">
                                                {{ __('Por favor, verifique la sintaxis del JSON.') }}
                                            </p>
                                        </div>
                                    @endif
                                </flux:field>
                            @endif
                        </div>
                    </x-ui.card>

                    {{-- Description Field --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Descripción') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Descripción de la configuración (traducible)') }}
                                </flux:text>
                            </div>
                            <flux:field>
                                <flux:label>{{ __('Descripción') }}</flux:label>
                                <flux:textarea 
                                    wire:model.live.blur="description" 
                                    placeholder="{{ __('Ingrese una descripción para esta configuración') }}"
                                    rows="3"
                                />
                                <flux:description>{{ __('Descripción que explica el propósito de esta configuración') }}</flux:description>
                                @error('description')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Translations Section --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Traducciones') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Gestiona las traducciones de la descripción y el valor (solo para tipo texto) en diferentes idiomas') }}
                                </flux:text>
                            </div>

                            @foreach($this->availableLanguages as $language)
                                @php
                                    $langCode = $language->code;
                                    $translationDescription = $translations[$langCode]['description'] ?? '';
                                    $translationValue = $translations[$langCode]['value'] ?? '';
                                @endphp
                                
                                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                    <div class="mb-4 flex items-center gap-2">
                                        <flux:heading size="xs">{{ $language->name }} ({{ strtoupper($langCode) }})</flux:heading>
                                        @if($langCode === getCurrentLanguageCode())
                                            <x-ui.badge color="blue" size="sm">{{ __('Idioma actual') }}</x-ui.badge>
                                        @endif
                                    </div>

                                    <div class="space-y-4">
                                        {{-- Translated Description --}}
                                        <flux:field>
                                            <flux:label>{{ __('Descripción traducida') }}</flux:label>
                                            <flux:textarea 
                                                wire:model.live.blur="translations.{{ $langCode }}.description" 
                                                placeholder="{{ __('Dejar vacío para usar la descripción por defecto') }}"
                                                rows="2"
                                            />
                                            <flux:description>
                                                {{ __('Si está vacío, se usará la descripción en el idioma por defecto') }}
                                            </flux:description>
                                        </flux:field>

                                        {{-- Translated Value (only for string type) --}}
                                        @if($setting->type === 'string')
                                            <flux:field>
                                                <flux:label>{{ __('Valor traducido') }}</flux:label>
                                                <flux:textarea 
                                                    wire:model.live.blur="translations.{{ $langCode }}.value" 
                                                    placeholder="{{ __('Dejar vacío para usar el valor por defecto') }}"
                                                    rows="3"
                                                />
                                                <flux:description>
                                                    {{ __('Si está vacío, se usará el valor en el idioma por defecto') }}
                                                </flux:description>
                                            </flux:field>
                                        @endif
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
                    {{-- Info Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $setting->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $setting->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($setting->updater)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Última actualización por') }}:</span>
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $setting->updater->name }}</span>
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
                                <flux:button 
                                    type="submit" 
                                    variant="primary"
                                    icon="check"
                                    class="w-full"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmUpdate,update"
                                >
                                    <span wire:loading.remove wire:target="confirmUpdate,update">
                                        {{ __('common.actions.save') }}
                                    </span>
                                    <span wire:loading wire:target="confirmUpdate,update">
                                        {{ __('Guardando...') }}
                                    </span>
                                </flux:button>

                                <flux:button 
                                    type="button"
                                    href="{{ route('admin.settings.index') }}" 
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

    {{-- Update Confirmation Modal --}}
    <flux:modal name="update-setting" wire:model.self="showUpdateModal">
        <form wire:submit="update" class="space-y-4">
            <flux:heading>{{ __('Confirmar Cambios') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas guardar los cambios en esta configuración?') }}
                <br>
                <strong><code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ $setting->key }}</code></strong>
            </flux:text>
            <flux:callout variant="info" class="mt-4">
                <flux:callout.text>
                    {{ __('Esta acción actualizará la configuración del sistema. Asegúrate de que los valores son correctos antes de continuar.') }}
                </flux:callout.text>
            </flux:callout>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showUpdateModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="primary"
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
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="setting-updated" variant="success" />
</div>
