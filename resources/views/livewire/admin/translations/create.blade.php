<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Crear Traducción') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Añade una nueva traducción para contenido en múltiples idiomas') }}
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.translations.index') }}" 
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
                ['label' => __('common.nav.translations'), 'href' => route('admin.translations.index'), 'icon' => 'language'],
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
                            {{-- Translatable Type Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Modelo Traducible') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('Selecciona el tipo de modelo que deseas traducir (Programa o Configuración)') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <select 
                                    wire:model.live="translatableType" 
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    required
                                >
                                    <option value="">{{ __('Selecciona un modelo...') }}</option>
                                    @foreach($this->getAvailableModels() as $modelClass => $modelName)
                                        <option value="{{ $modelClass }}" @selected($translatableType === $modelClass)>
                                            {{ $modelName }}
                                        </option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Tipo de modelo que se va a traducir') }}</flux:description>
                                @error('translatable_type')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Translatable ID Field (dynamic) --}}
                            @if($translatableType)
                                <flux:field>
                                    <flux:label>
                                        {{ __('Registro') }} <span class="text-red-500">*</span>
                                        <flux:tooltip content="{{ __('Selecciona el registro específico del modelo que deseas traducir') }}" position="top">
                                            <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                        </flux:tooltip>
                                    </flux:label>
                                    <select 
                                        wire:model.live.debounce.300ms="translatableId" 
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                        required
                                    >
                                        <option value="">{{ __('Selecciona un registro...') }}</option>
                                        @foreach($this->getTranslatableOptions() as $option)
                                            <option value="{{ $option->id }}" @selected($translatableId == $option->id)>
                                                @if($translatableType === \App\Models\Program::class)
                                                    {{ $option->code }} - {{ $option->name }}
                                                @elseif($translatableType === \App\Models\Setting::class)
                                                    {{ $option->key }}
                                                @else
                                                    {{ $option->id }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <flux:description>
                                        @if($translatableType === \App\Models\Program::class)
                                            {{ __('Selecciona el programa que deseas traducir') }}
                                        @elseif($translatableType === \App\Models\Setting::class)
                                            {{ __('Selecciona la configuración que deseas traducir') }}
                                        @endif
                                    </flux:description>
                                    @error('translatable_id')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            @endif

                            {{-- Language Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Idioma') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('Selecciona el idioma para esta traducción') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                    <select 
                                        wire:model.live.debounce.300ms="languageId" 
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                        required
                                    >
                                    <option value="">{{ __('Selecciona un idioma...') }}</option>
                                    @foreach($this->getLanguages() as $language)
                                        <option value="{{ $language->id }}" @selected($languageId == $language->id)>
                                            {{ $language->name }} ({{ $language->code }})
                                            @if($language->is_default)
                                                - {{ __('Por defecto') }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Idioma de la traducción') }}</flux:description>
                                @error('language_id')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Field Field (dynamic based on model) --}}
                            @if($translatableType && $translatableId)
                                <flux:field>
                                    <flux:label>
                                        {{ __('Campo') }} <span class="text-red-500">*</span>
                                        <flux:tooltip content="{{ __('Selecciona el campo del modelo que deseas traducir') }}" position="top">
                                            <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                        </flux:tooltip>
                                    </flux:label>
                                    <select 
                                        wire:model.live.debounce.300ms="field" 
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                        required
                                    >
                                        <option value="">{{ __('Selecciona un campo...') }}</option>
                                        @foreach($this->getAvailableFields() as $fieldKey => $fieldLabel)
                                            <option value="{{ $fieldKey }}" @selected($field === $fieldKey)>
                                                {{ $fieldLabel }} ({{ $fieldKey }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <flux:description>
                                        @if($translatableType === \App\Models\Program::class)
                                            {{ __('Campo del programa a traducir (nombre o descripción)') }}
                                        @elseif($translatableType === \App\Models\Setting::class)
                                            {{ __('Campo de la configuración a traducir (valor)') }}
                                        @endif
                                    </flux:description>
                                    @error('field')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            @endif

                            {{-- Warning if translation already exists --}}
                            @if($translatableType && $translatableId && $languageId && $field && $this->translationExists())
                                @php
                                    $existingTranslation = $this->getExistingTranslation();
                                @endphp
                                <flux:callout variant="warning" icon="exclamation-triangle">
                                    <flux:heading size="sm">{{ __('Advertencia: Traducción Existente') }}</flux:heading>
                                    <p class="mt-2 text-sm">
                                        {{ __('Ya existe una traducción para esta combinación de modelo, idioma y campo.') }}
                                    </p>
                                    @if($existingTranslation)
                                        <div class="mt-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                                {{ __('Traducción actual:') }}
                                            </p>
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ \Illuminate\Support\Str::limit($existingTranslation->value, 100) }}
                                            </p>
                                            <div class="mt-2">
                                                <flux:button 
                                                    href="{{ route('admin.translations.edit', $existingTranslation) }}" 
                                                    variant="ghost"
                                                    size="sm"
                                                    wire:navigate
                                                    icon="pencil"
                                                >
                                                    {{ __('Editar traducción existente') }}
                                                </flux:button>
                                            </div>
                                        </div>
                                    @endif
                                </flux:callout>
                            @endif

                            {{-- Value Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Valor de la Traducción') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('Introduce el texto traducido para el campo seleccionado') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:textarea 
                                    wire:model.blur="value" 
                                    placeholder="{{ __('Introduce el texto traducido...') }}"
                                    required
                                    rows="6"
                                    :disabled="$this->translationExists()"
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
                                    :disabled="$this->translationExists()"
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

                    {{-- Info Card --}}
                    @if($translatableType && $translatableId && $field)
                        <x-ui.card>
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Modelo:') }}</span>
                                        <span class="ml-2 text-zinc-600 dark:text-zinc-400">
                                            {{ $this->getAvailableModels()[$translatableType] ?? '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Registro:') }}</span>
                                        <span class="ml-2 text-zinc-600 dark:text-zinc-400">
                                            @php
                                                $option = $this->getTranslatableOptions()->firstWhere('id', $translatableId);
                                            @endphp
                                            @if($option)
                                                @if($translatableType === \App\Models\Program::class)
                                                    {{ $option->code }} - {{ $option->name }}
                                                @elseif($translatableType === \App\Models\Setting::class)
                                                    {{ $option->key }}
                                                @endif
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Campo:') }}</span>
                                        <span class="ml-2 text-zinc-600 dark:text-zinc-400">
                                            <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-700">{{ $field }}</code>
                                        </span>
                                    </div>
                                    @if($languageId)
                                        @php
                                            $language = $this->getLanguages()->firstWhere('id', $languageId);
                                        @endphp
                                        @if($language)
                                            <div>
                                                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Idioma:') }}</span>
                                                <span class="ml-2 text-zinc-600 dark:text-zinc-400">
                                                    {{ $language->name }} ({{ $language->code }})
                                                </span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </x-ui.card>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Toast Notifications --}}
    <x-ui.toast event="translation-created" variant="success" />
</div>
