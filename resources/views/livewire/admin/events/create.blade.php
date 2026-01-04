<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Crear Evento') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Añade un nuevo evento Erasmus+ al sistema') }}
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.events.index') }}" 
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
                ['label' => __('Eventos Erasmus+'), 'href' => route('admin.events.index'), 'icon' => 'calendar'],
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
                    {{-- Basic Information Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Información Básica') }}</flux:heading>
                            </div>

                            {{-- Title Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Título') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('El título debe ser descriptivo y claro. Se mostrará en el listado de eventos y en el calendario.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="title" 
                                    placeholder="Ej: Apertura de convocatoria Erasmus+ 2025"
                                    required
                                    autofocus
                                    maxlength="255"
                                />
                                <flux:description>{{ __('Título descriptivo del evento (máximo 255 caracteres)') }}</flux:description>
                                @error('title')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Description Field --}}
                            <flux:field>
                                <flux:label>{{ __('Descripción') }}</flux:label>
                                <flux:textarea 
                                    wire:model.live.blur="description" 
                                    placeholder="Descripción detallada del evento..."
                                    rows="6"
                                />
                                <flux:description>{{ __('Descripción completa del evento (opcional)') }}</flux:description>
                                @error('description')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Event Type Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Tipo de Evento') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('Selecciona el tipo de evento según su naturaleza. Esto ayudará a categorizar y filtrar los eventos.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <select 
                                    wire:model.live="event_type"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    required
                                >
                                    <option value="">{{ __('Selecciona un tipo') }}</option>
                                    @foreach($this->eventTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Tipo de evento según su naturaleza') }}</flux:description>
                                @error('event_type')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Dates Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Fechas y Horarios') }}</flux:heading>
                            </div>

                            {{-- All Day Toggle --}}
                            <flux:field>
                                <flux:checkbox wire:model.live="is_all_day">
                                    {{ __('Evento de todo el día') }}
                                </flux:checkbox>
                                <flux:description>{{ __('Si está marcado, el evento no tendrá hora específica') }}</flux:description>
                            </flux:field>

                            <div class="grid gap-4 sm:grid-cols-2">
                                {{-- Start Date Field --}}
                                <flux:field>
                                    <flux:label>{{ __('Fecha de Inicio') }} <span class="text-red-500">*</span></flux:label>
                                    <input 
                                        type="datetime-local" 
                                        wire:model.live="start_date"
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                        required
                                    />
                                    <flux:description>{{ __('Fecha y hora de inicio del evento') }}</flux:description>
                                    @error('start_date')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                {{-- End Date Field --}}
                                <flux:field>
                                    <flux:label>{{ __('Fecha de Fin') }}</flux:label>
                                    <input 
                                        type="datetime-local" 
                                        wire:model.live="end_date"
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    />
                                    <flux:description>{{ __('Fecha y hora de fin del evento (opcional)') }}</flux:description>
                                    @error('end_date')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Associations Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Asociaciones') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Asocia este evento a un programa y/o convocatoria (opcional)') }}
                                </flux:text>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                {{-- Program Field --}}
                                <flux:field>
                                    <flux:label>
                                        {{ __('Programa') }}
                                        <flux:tooltip content="{{ __('Asocia el evento a un programa específico. Esto permitirá filtrar eventos por programa.') }}" position="top">
                                            <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                        </flux:tooltip>
                                    </flux:label>
                                    <select 
                                        wire:model.live="program_id"
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    >
                                        <option value="">{{ __('Ninguno') }}</option>
                                        @foreach($this->availablePrograms as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                    <flux:description>{{ __('Programa al que pertenece el evento (opcional)') }}</flux:description>
                                    @error('program_id')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                {{-- Call Field --}}
                                <flux:field>
                                    <flux:label>
                                        {{ __('Convocatoria') }}
                                        <flux:tooltip content="{{ __('Asocia el evento a una convocatoria específica. Solo se mostrarán las convocatorias del programa seleccionado.') }}" position="top">
                                            <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                        </flux:tooltip>
                                    </flux:label>
                                    <select 
                                        wire:model.live="call_id"
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="!$program_id"
                                    >
                                        <option value="">{{ __('Ninguna') }}</option>
                                        @foreach($this->availableCalls as $call)
                                            <option value="{{ $call->id }}">{{ $call->title }}</option>
                                        @endforeach
                                    </select>
                                    <flux:description>
                                        {{ $program_id ? __('Convocatoria del programa seleccionado (opcional)') : __('Primero selecciona un programa') }}
                                    </flux:description>
                                    @error('call_id')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Location Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Ubicación') }}</flux:heading>
                            </div>

                            {{-- Location Field --}}
                            <flux:field>
                                <flux:label>{{ __('Ubicación') }}</flux:label>
                                <flux:input 
                                    wire:model.live.blur="location" 
                                    placeholder="Ej: Aula 101, Edificio A"
                                />
                                <flux:description>{{ __('Lugar donde se realizará el evento (opcional)') }}</flux:description>
                                @error('location')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Images Upload Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Imágenes del Evento') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Puedes subir múltiples imágenes para el evento (opcional)') }}
                                </flux:text>
                            </div>

                            {{-- Image Previews --}}
                            @if(!empty($imagePreviews))
                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                    @foreach($imagePreviews as $index => $preview)
                                        <div class="relative">
                                            <img 
                                                src="{{ $preview }}" 
                                                alt="{{ __('Vista previa') }}"
                                                class="h-32 w-full rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                            />
                                            <button 
                                                type="button"
                                                wire:click="removeImage({{ $index }})"
                                                class="absolute top-2 right-2 rounded-full bg-red-500 p-1.5 text-white shadow-lg transition-colors hover:bg-red-600"
                                                aria-label="{{ __('Eliminar imagen') }}"
                                            >
                                                <flux:icon name="x-mark" class="[:where(&)]:size-4" variant="solid" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Filepond Upload --}}
                            <flux:field>
                                <flux:label>{{ __('Añadir imágenes') }}</flux:label>
                                
                                <x-filepond::upload 
                                    wire:model="images"
                                    accepted-file-types="image/jpeg,image/png,image/webp,image/gif"
                                    max-file-size="5MB"
                                    multiple
                                    label-idle='{{ __("Arrastra tus imágenes aquí o") }} <span class="filepond--label-action">{{ __("selecciona") }}</span>'
                                    label-file-type-not-allowed="{{ __('Solo se permiten archivos de imagen (JPEG, PNG, WebP, GIF)') }}"
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
                                    {{ __('Formatos aceptados: JPEG, PNG, WebP, GIF. Tamaño máximo por imagen: 5MB. Puedes seleccionar múltiples archivos.') }}
                                </flux:description>
                                
                                @error('images.*')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Visibility Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Visibilidad') }}</flux:heading>
                            </div>

                            {{-- Public Toggle --}}
                            <flux:field>
                                <flux:checkbox wire:model.live="is_public">
                                    {{ __('Evento público') }}
                                    <flux:tooltip content="{{ __('Los eventos públicos serán visibles para todos los usuarios en el área pública. Los eventos privados solo serán visibles en el panel de administración.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:checkbox>
                                <flux:description>{{ __('Los eventos públicos se mostrarán en el área pública del sitio') }}</flux:description>
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
                                    href="{{ route('admin.events.index') }}" 
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
    <x-ui.toast event="event-created" variant="success" />
</div>
