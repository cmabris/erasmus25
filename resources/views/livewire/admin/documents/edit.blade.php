<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Documento') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información del documento') }}: <strong>{{ $document->title }}</strong>
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.documents.index') }}" 
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
                ['label' => __('common.nav.documents'), 'href' => route('admin.documents.index'), 'icon' => 'document'],
                ['label' => $document->title, 'href' => route('admin.documents.show', $document), 'icon' => 'document'],
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
                            {{-- Category Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Categoría') }} <span class="text-red-500">*</span>
                                </flux:label>
                                <select 
                                    wire:model.live.blur="categoryId" 
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    required
                                >
                                    <option value="">{{ __('Seleccionar categoría') }}</option>
                                    @foreach($this->categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Selecciona la categoría a la que pertenece este documento') }}</flux:description>
                                @error('categoryId')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Title Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Título') }} <span class="text-red-500">*</span>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="title" 
                                    placeholder="Ej: Convocatoria Erasmus+ 2024-2025"
                                    required
                                    autofocus
                                    maxlength="255"
                                />
                                <flux:description>{{ __('El título del documento que se mostrará públicamente') }}</flux:description>
                                @error('title')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Slug Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Slug') }}
                                    <flux:tooltip content="{{ __('El slug se genera automáticamente desde el título, pero puedes editarlo manualmente si lo deseas. Debe ser único y solo contener letras minúsculas, números y guiones.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="slug" 
                                    placeholder="Se genera automáticamente"
                                    maxlength="255"
                                />
                                <flux:description>{{ __('URL amigable para el documento (se genera automáticamente desde el título)') }}</flux:description>
                                @error('slug')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Description Field --}}
                            <flux:field>
                                <flux:label>{{ __('Descripción') }}</flux:label>
                                <flux:textarea 
                                    wire:model.blur="description" 
                                    placeholder="Descripción del documento (opcional)"
                                    rows="4"
                                />
                                <flux:description>{{ __('Descripción detallada del documento') }}</flux:description>
                                @error('description')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Document Type Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Tipo de Documento') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('El tipo de documento ayuda a categorizar y organizar los documentos. Los tipos disponibles son: Convocatoria, Modelo, Seguro, Consentimiento, Guía, FAQ u Otro.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <select 
                                    wire:model.live.blur="documentType" 
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    required
                                >
                                    @foreach($this->getDocumentTypeOptions() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Selecciona el tipo de documento') }}</flux:description>
                                @error('documentType')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Version Field --}}
                            <flux:field>
                                <flux:label>{{ __('Versión') }}</flux:label>
                                <flux:input 
                                    wire:model.blur="version" 
                                    placeholder="Ej: 1.0, 2.1, etc."
                                    maxlength="255"
                                />
                                <flux:description>{{ __('Versión del documento (opcional)') }}</flux:description>
                                @error('version')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- File Upload Card --}}
                    <x-ui.card>
                        <div class="mb-4">
                            <flux:heading size="sm">{{ __('Archivo del Documento') }}</flux:heading>
                            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Gestiona el archivo del documento') }}
                            </flux:text>
                        </div>
                        <div class="space-y-4">
                            @if($this->existingFile && !$removeExistingFile)
                                {{-- Existing File --}}
                                <flux:field>
                                    <flux:label>{{ __('Archivo Actual') }}</flux:label>
                                    <div class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                        @if(str_starts_with($this->existingFile->mime_type, 'image/'))
                                            <img 
                                                src="{{ $this->existingFile->getUrl() }}" 
                                                alt="{{ $document->title }}"
                                                class="h-16 w-16 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                                loading="lazy"
                                            />
                                        @else
                                            <flux:icon name="document" class="[:where(&)]:size-8 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                        @endif
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $this->existingFile->file_name }}
                                            </p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ number_format($this->existingFile->size / 1024, 2) }} KB
                                            </p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $this->existingFile->mime_type }}
                                            </p>
                                        </div>
                                        <div class="flex gap-2">
                                            <flux:button 
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                href="{{ $this->existingFile->getUrl() }}"
                                                target="_blank"
                                                icon="eye"
                                            >
                                                {{ __('Ver') }}
                                            </flux:button>
                                            <flux:button 
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                href="{{ $this->existingFile->getUrl() }}"
                                                download
                                                icon="arrow-down-tray"
                                            >
                                                {{ __('Descargar') }}
                                            </flux:button>
                                            <flux:button 
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                wire:click="removeFile"
                                                icon="trash"
                                            >
                                                {{ __('Eliminar') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                    <flux:description>
                                        {{ __('Puedes eliminar este archivo y subir uno nuevo, o mantenerlo.') }}
                                    </flux:description>
                                </flux:field>
                            @else
                                {{-- Upload New File --}}
                                <flux:field>
                                    <flux:label>{{ __('Archivo del Documento') }}</flux:label>
                                    
                                    <x-filepond::upload 
                                        wire:model="file"
                                        accepted-file-types="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv,image/jpeg,image/png,image/webp"
                                        max-file-size="20MB"
                                        label-idle='{{ __("Arrastra tu archivo aquí o") }} <span class="filepond--label-action">{{ __("selecciona") }}</span>'
                                        label-file-type-not-allowed="{{ __('Solo se permiten archivos PDF, Word, Excel, PowerPoint, texto, CSV o imágenes (JPEG, PNG, WebP)') }}"
                                        label-file-size-too-large="{{ __('El archivo es demasiado grande (máximo 20MB)') }}"
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
                                        {{ __('Formatos aceptados: PDF, Word, Excel, PowerPoint, texto, CSV, imágenes (JPEG, PNG, WebP). Tamaño máximo: 20MB') }}
                                    </flux:description>
                                    
                                    @error('file')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            @endif
                        </div>
                    </x-ui.card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Additional Information Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información Adicional') }}</flux:heading>
                            </div>

                            {{-- Program Field --}}
                            <flux:field>
                                <flux:label>{{ __('Programa') }}</flux:label>
                                <select 
                                    wire:model.live.blur="programId" 
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                >
                                    <option value="">{{ __('Sin programa específico') }}</option>
                                    @foreach($this->programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Asocia el documento a un programa específico (opcional)') }}</flux:description>
                                @error('programId')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Academic Year Field --}}
                            <flux:field>
                                <flux:label>{{ __('Año Académico') }}</flux:label>
                                <select 
                                    wire:model.live.blur="academicYearId" 
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                >
                                    <option value="">{{ __('Sin año académico específico') }}</option>
                                    @foreach($this->academicYears as $academicYear)
                                        <option value="{{ $academicYear->id }}">{{ $academicYear->year }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Asocia el documento a un año académico específico (opcional)') }}</flux:description>
                                @error('academicYearId')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Active Status Field --}}
                            <flux:field>
                                <flux:label>{{ __('Estado') }}</flux:label>
                                <flux:switch 
                                    wire:model.live="isActive"
                                    :label="$isActive ? __('Activo') : __('Inactivo')"
                                />
                                <flux:description>{{ __('Los documentos inactivos no se mostrarán públicamente') }}</flux:description>
                                @error('isActive')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Document Info Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información del Documento') }}</flux:heading>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Creado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $document->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $document->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Descargas') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($document->download_count, 0, ',', '.') }}</span>
                                </div>
                                @if($document->creator)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Creado por') }}:</span>
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $document->creator->name }}</span>
                                    </div>
                                @endif
                                @if($document->updater)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Actualizado por') }}:</span>
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $document->updater->name }}</span>
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
                                    href="{{ route('admin.documents.show', $document) }}" 
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
    <x-ui.toast event="document-updated" variant="success" />
</div>
