<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Evento') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información del evento') }}
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.events.show', $event) }}" 
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
                ['label' => $event->title, 'href' => route('admin.events.show', $event), 'icon' => 'eye'],
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

                    {{-- Images Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Imágenes del Evento') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Gestiona las imágenes del evento. Puedes añadir nuevas, eliminar o restaurar existentes.') }}
                                </flux:text>
                            </div>

                            {{-- Existing Images --}}
                            @if($this->existingImages->isNotEmpty())
                                <div>
                                    <flux:text class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ __('Imágenes actuales') }}
                                    </flux:text>
                                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                        @foreach($this->existingImages as $media)
                                            <div class="relative">
                                                <img 
                                                    src="{{ $media->getUrl('thumbnail') ?? $media->getUrl() }}" 
                                                    alt="{{ $media->name ?? __('Imagen') }}"
                                                    class="h-32 w-full rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                                    loading="lazy"
                                                />
                                                <button 
                                                    type="button"
                                                    wire:click="confirmDeleteImage({{ $media->id }})"
                                                    class="absolute top-2 right-2 rounded-full bg-red-500 p-1.5 text-white shadow-lg transition-colors hover:bg-red-600"
                                                    aria-label="{{ __('Eliminar imagen') }}"
                                                >
                                                    <flux:icon name="trash" class="[:where(&)]:size-4" variant="solid" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Soft-Deleted Images (if any) --}}
                            @if($this->deletedImages->isNotEmpty())
                                <div>
                                    <flux:callout variant="warning" class="mb-3">
                                        <flux:callout.text>
                                            {{ __('Hay imágenes eliminadas que pueden ser restauradas.') }}
                                        </flux:callout.text>
                                    </flux:callout>
                                    <flux:text class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ __('Imágenes eliminadas (puedes restaurarlas)') }}
                                    </flux:text>
                                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                        @foreach($this->deletedImages as $media)
                                            <div class="relative opacity-60">
                                                <img 
                                                    src="{{ $media->getUrl('thumbnail') ?? $media->getUrl() }}" 
                                                    alt="{{ $media->name ?? __('Imagen eliminada') }}"
                                                    class="h-32 w-full rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                                    loading="lazy"
                                                />
                                                <div class="absolute inset-0 flex items-center justify-center gap-2">
                                                    <button 
                                                        type="button"
                                                        wire:click="restoreImage({{ $media->id }})"
                                                        class="rounded-full bg-green-500 p-1.5 text-white shadow-lg transition-colors hover:bg-green-600"
                                                        aria-label="{{ __('Restaurar imagen') }}"
                                                        :tooltip="__('Restaurar imagen')"
                                                    >
                                                        <flux:icon name="arrow-path" class="[:where(&)]:size-4" variant="solid" />
                                                    </button>
                                                    @can('forceDelete', $event)
                                                        <button 
                                                            type="button"
                                                            wire:click="confirmForceDeleteImage({{ $media->id }})"
                                                            class="rounded-full bg-red-500 p-1.5 text-white shadow-lg transition-colors hover:bg-red-600"
                                                            aria-label="{{ __('Eliminar permanentemente') }}"
                                                            :tooltip="__('Eliminar permanentemente')"
                                                        >
                                                            <flux:icon name="trash" class="[:where(&)]:size-4" variant="solid" />
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Filepond Upload --}}
                            <flux:field>
                                <flux:label>{{ __('Añadir nuevas imágenes') }}</flux:label>
                                
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
                                    wire:target="update"
                                >
                                    <span wire:loading.remove wire:target="update">
                                        {{ __('common.actions.update') }}
                                    </span>
                                    <span wire:loading wire:target="update">
                                        {{ __('Actualizando...') }}
                                    </span>
                                </flux:button>

                                <flux:button 
                                    type="button"
                                    href="{{ route('admin.events.show', $event) }}" 
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

    {{-- Delete Image Confirmation Modal --}}
    <flux:modal name="delete-image" wire:model.self="showDeleteImageModal">
        <form wire:submit="deleteImage">
            <flux:heading>{{ __('Eliminar Imagen') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta imagen?') }}
            </flux:text>
            <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('La imagen se marcará como eliminada, pero podrás restaurarla más tarde.') }}
            </flux:text>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteImageModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="danger"
                    wire:loading.attr="disabled"
                    wire:target="deleteImage"
                >
                    <span wire:loading.remove wire:target="deleteImage">
                        {{ __('Eliminar') }}
                    </span>
                    <span wire:loading wire:target="deleteImage">
                        {{ __('Eliminando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Image Confirmation Modal --}}
    <flux:modal name="force-delete-image" wire:model.self="showForceDeleteImageModal">
        <form wire:submit="forceDeleteImage" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente esta imagen?') }}
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente. La imagen se eliminará completamente y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteImageModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button
                    type="submit"
                    variant="danger"
                    wire:loading.attr="disabled"
                    wire:target="forceDeleteImage"
                >
                    <span wire:loading.remove wire:target="forceDeleteImage">
                        {{ __('Eliminar permanentemente') }}
                    </span>
                    <span wire:loading wire:target="forceDeleteImage">
                        {{ __('Eliminando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="event-updated" variant="success" />
    <x-ui.toast event="image-deleted" variant="success" />
    <x-ui.toast event="image-restored" variant="success" />
    <x-ui.toast event="image-force-deleted" variant="warning" />
</div>
