<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Noticia') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Edita la información de la noticia o experiencia Erasmus+') }}
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.news.index') }}" 
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
                ['label' => __('common.nav.news'), 'href' => route('admin.news.index'), 'icon' => 'newspaper'],
                ['label' => $newsPost->title, 'icon' => 'pencil'],
            ]"
        />
    </div>

    {{-- Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <form wire:submit="update" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Main Form Fields --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Basic Information --}}
                    <x-ui.card>
                        <div class="mb-4">
                            <flux:heading size="sm">{{ __('Información Básica') }}</flux:heading>
                        </div>
                        <div class="space-y-6">
                            {{-- Program --}}
                            <flux:field>
                                <flux:label>{{ __('Programa') }}</flux:label>
                                <select wire:model.live.blur="program_id" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                    <option value="0">{{ __('Selecciona un programa (opcional)') }}</option>
                                    @foreach($this->programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Programa asociado a la noticia (opcional)') }}</flux:description>
                                @error('program_id')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Academic Year --}}
                            <flux:field>
                                <flux:label>{{ __('Año Académico') }} <span class="text-red-500">*</span></flux:label>
                                <select wire:model.live.blur="academic_year_id" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" required>
                                    <option value="0">{{ __('Selecciona un año académico') }}</option>
                                    @foreach($this->academicYears as $academicYear)
                                        <option value="{{ $academicYear->id }}">{{ $academicYear->year }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Año académico al que pertenece la noticia') }}</flux:description>
                                @error('academic_year_id')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Title --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Título') }} <span class="text-red-500">*</span>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="title" 
                                    placeholder="{{ __('Ej: Mi experiencia Erasmus+ en París') }}"
                                    required
                                    autofocus
                                />
                                <flux:description>{{ __('Título descriptivo de la noticia o experiencia') }}</flux:description>
                                @error('title')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Slug --}}
                            <flux:field>
                                <flux:label>{{ __('Slug') }}</flux:label>
                                <flux:input 
                                    wire:model.blur="slug" 
                                    placeholder="{{ __('Se genera automáticamente desde el título') }}"
                                />
                                <flux:description>{{ __('URL amigable (se genera automáticamente si se deja vacío)') }}</flux:description>
                                @error('slug')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Excerpt --}}
                            <flux:field>
                                <flux:label>{{ __('Extracto') }}</flux:label>
                                <flux:textarea 
                                    wire:model.live.blur="excerpt" 
                                    placeholder="{{ __('Breve descripción o resumen de la noticia...') }}"
                                    rows="3"
                                />
                                <flux:description>{{ __('Resumen breve que aparecerá en listados y previews') }}</flux:description>
                                @error('excerpt')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Content --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Contenido') }} <span class="text-red-500">*</span>
                                </flux:label>
                                <flux:textarea 
                                    wire:model.live.blur="content" 
                                    placeholder="{{ __('Escribe aquí el contenido completo de la noticia...') }}"
                                    rows="12"
                                    required
                                />
                                <flux:description>
                                    {{ __('Contenido completo de la noticia. Por ahora se usa texto plano. Se implementará editor enriquecido más adelante.') }}
                                </flux:description>
                                @error('content')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Mobility Information --}}
                    <x-ui.card>
                        <div class="mb-4">
                            <flux:heading size="sm">{{ __('Información de Movilidad') }}</flux:heading>
                            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Información opcional sobre la movilidad asociada') }}
                            </flux:text>
                        </div>
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                {{-- Country --}}
                                <flux:field>
                                    <flux:label>{{ __('País') }}</flux:label>
                                    <flux:input 
                                        wire:model.live.blur="country" 
                                        placeholder="{{ __('Ej: Francia') }}"
                                    />
                                    @error('country')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                {{-- City --}}
                                <flux:field>
                                    <flux:label>{{ __('Ciudad') }}</flux:label>
                                    <flux:input 
                                        wire:model.live.blur="city" 
                                        placeholder="{{ __('Ej: París') }}"
                                    />
                                    @error('city')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>

                            {{-- Host Entity --}}
                            <flux:field>
                                <flux:label>{{ __('Entidad de Acogida') }}</flux:label>
                                <flux:input 
                                    wire:model.live.blur="host_entity" 
                                    placeholder="{{ __('Ej: Universidad de París') }}"
                                />
                                @error('host_entity')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                {{-- Mobility Type --}}
                                <flux:field>
                                    <flux:label>{{ __('Tipo de Movilidad') }}</flux:label>
                                    <select wire:model.live.blur="mobility_type" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                        <option value="">{{ __('Selecciona un tipo') }}</option>
                                        <option value="alumnado">{{ __('Alumnado') }}</option>
                                        <option value="personal">{{ __('Personal') }}</option>
                                    </select>
                                    @error('mobility_type')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                {{-- Mobility Category --}}
                                <flux:field>
                                    <flux:label>{{ __('Categoría de Movilidad') }}</flux:label>
                                    <select wire:model.live.blur="mobility_category" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                        <option value="">{{ __('Selecciona una categoría') }}</option>
                                        <option value="FCT">{{ __('FCT (Formación en Centros de Trabajo)') }}</option>
                                        <option value="job_shadowing">{{ __('Job Shadowing') }}</option>
                                        <option value="intercambio">{{ __('Intercambio') }}</option>
                                        <option value="curso">{{ __('Curso') }}</option>
                                        <option value="otro">{{ __('Otro') }}</option>
                                    </select>
                                    @error('mobility_category')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Tags --}}
                    <x-ui.card>
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <flux:heading size="sm">{{ __('Etiquetas') }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Selecciona las etiquetas relacionadas con esta noticia') }}
                                </flux:text>
                            </div>
                            @can('create', \App\Models\NewsTag::class)
                                <flux:button 
                                    type="button"
                                    wire:click="$set('showCreateTagModal', true)"
                                    variant="ghost"
                                    size="sm"
                                    icon="plus"
                                >
                                    {{ __('Crear Etiqueta') }}
                                </flux:button>
                            @endcan
                        </div>

                        {{-- Tag Search --}}
                        <div class="mb-4">
                            <flux:field>
                                <flux:input 
                                    wire:model.live.debounce.300ms="tagSearch" 
                                    placeholder="{{ __('Buscar etiquetas...') }}"
                                    icon="magnifying-glass"
                                />
                            </flux:field>
                        </div>

                        @if($this->availableTags->isNotEmpty())
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($this->availableTags as $tag)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 p-3 transition-colors hover:border-erasmus-300 hover:bg-erasmus-50 dark:border-zinc-700 dark:hover:border-erasmus-600 dark:hover:bg-erasmus-900/10">
                                        <input 
                                            type="checkbox"
                                            wire:model.live="selectedTags"
                                            value="{{ $tag->id }}"
                                            class="mt-0.5 size-4 rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                                        />
                                        <div class="flex-1">
                                            <div class="font-medium text-zinc-900 dark:text-white">
                                                {{ $tag->name }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    @if($tagSearch)
                                        {{ __('No se encontraron etiquetas que coincidan con la búsqueda') }}
                                    @else
                                        {{ __('No hay etiquetas disponibles') }}
                                    @endif
                                </flux:text>
                            </div>
                        @endif

                        @if(count($selectedTags) > 0)
                            <div class="mt-4 rounded-lg border border-erasmus-200 bg-erasmus-50 p-3 dark:border-erasmus-700 dark:bg-erasmus-900/20">
                                <p class="text-xs font-medium text-erasmus-700 dark:text-erasmus-300">
                                    {{ __('Etiquetas seleccionadas') }}: <strong>{{ count($selectedTags) }}</strong>
                                </p>
                            </div>
                        @endif

                        @error('selectedTags')
                            <flux:error class="mt-2">{{ $message }}</flux:error>
                        @enderror
                    </x-ui.card>

                    {{-- Featured Image --}}
                    <x-ui.card>
                        <div class="mb-4">
                            <flux:heading size="sm">{{ __('Imagen Destacada') }}</flux:heading>
                            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Sube una nueva imagen o mantén la actual') }}
                            </flux:text>
                        </div>
                        <div class="space-y-4">
                            {{-- Current Image --}}
                            @if($this->hasExistingFeaturedImage() && !$removeFeaturedImage && !$featuredImage)
                                <flux:field>
                                    <flux:label>{{ __('Imagen Actual') }}</flux:label>
                                    <div class="flex items-center gap-4 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                        <img 
                                            src="{{ $featuredImageUrl }}" 
                                            alt="{{ $newsPost->title }}"
                                            class="h-20 w-20 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                        />
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ __('Imagen destacada actual') }}
                                            </p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ __('La imagen se mostrará en la vista pública de la noticia') }}
                                            </p>
                                        </div>
                                        <div class="flex gap-2">
                                            <flux:button 
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                href="{{ $featuredImageUrl }}"
                                                target="_blank"
                                            >
                                                {{ __('Ver') }}
                                            </flux:button>
                                            <flux:button 
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                wire:click="toggleRemoveFeaturedImage"
                                            >
                                                {{ __('Eliminar') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </flux:field>
                            @endif

                            {{-- Upload de Nueva Imagen --}}
                            @if(!$removeFeaturedImage)
                                <flux:field>
                                    <flux:label>
                                        @if($this->hasExistingFeaturedImage() && !$featuredImage)
                                            {{ __('Reemplazar imagen') }}
                                        @else
                                            {{ __('Seleccionar imagen') }}
                                        @endif
                                    </flux:label>
                                    
                                    <x-filepond::upload 
                                        wire:model="featuredImage"
                                        accepted-file-types="image/jpeg,image/png,image/webp,image/gif"
                                        max-file-size="5MB"
                                        label-idle='{{ __("Arrastra tu imagen aquí o") }} <span class="filepond--label-action">{{ __("selecciona") }}</span>'
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
                                        {{ __('Formatos aceptados: JPEG, PNG, WebP, GIF. Tamaño máximo: 5MB') }}
                                    </flux:description>
                                    
                                    @error('featuredImage')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            @endif
                        </div>
                    </x-ui.card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Status and Publication --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Estado y Publicación') }}</flux:heading>
                            </div>

                            {{-- Status --}}
                            <flux:field>
                                <flux:label>{{ __('Estado') }} <span class="text-red-500">*</span></flux:label>
                                <select wire:model.live.blur="status" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" required>
                                    <option value="borrador">{{ __('Borrador') }}</option>
                                    <option value="en_revision">{{ __('En Revisión') }}</option>
                                    <option value="publicado">{{ __('Publicado') }}</option>
                                    <option value="archivado">{{ __('Archivado') }}</option>
                                </select>
                                <flux:description>{{ __('Estado actual de la noticia') }}</flux:description>
                                @error('status')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Published At --}}
                            <flux:field>
                                <flux:label>{{ __('Fecha de Publicación') }}</flux:label>
                                <flux:input 
                                    wire:model.live.blur="published_at" 
                                    type="datetime-local"
                                />
                                <flux:description>{{ __('Fecha y hora de publicación (opcional, se establece automáticamente al publicar)') }}</flux:description>
                                @error('published_at')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Additional Information --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información Adicional') }}</flux:heading>
                            </div>

                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="font-medium text-zinc-600 dark:text-zinc-400">{{ __('Autor:') }}</span>
                                    <span class="text-zinc-900 dark:text-white">{{ $newsPost->author->name ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-zinc-600 dark:text-zinc-400">{{ __('Creado:') }}</span>
                                    <span class="text-zinc-900 dark:text-white">{{ $newsPost->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-zinc-600 dark:text-zinc-400">{{ __('Actualizado:') }}</span>
                                    <span class="text-zinc-900 dark:text-white">{{ $newsPost->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($newsPost->reviewer)
                                    <div>
                                        <span class="font-medium text-zinc-600 dark:text-zinc-400">{{ __('Revisor:') }}</span>
                                        <span class="text-zinc-900 dark:text-white">{{ $newsPost->reviewer->name }}</span>
                                    </div>
                                @endif
                                @if($newsPost->reviewed_at)
                                    <div>
                                        <span class="font-medium text-zinc-600 dark:text-zinc-400">{{ __('Revisado:') }}</span>
                                        <span class="text-zinc-900 dark:text-white">{{ $newsPost->reviewed_at->format('d/m/Y H:i') }}</span>
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
                                    href="{{ route('admin.news.index') }}" 
                                    variant="ghost"
                                    wire:navigate
                                    class="w-full"
                                >
                                    {{ __('common.actions.cancel') }}
                                </flux:button>

                                @can('delete', $newsPost)
                                    <flux:button 
                                        type="button"
                                        wire:click="$set('showDeleteModal', true)"
                                        variant="danger"
                                        icon="trash"
                                        class="w-full"
                                    >
                                        {{ __('common.actions.delete') }}
                                    </flux:button>
                                @endcan
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </form>
    </div>

    {{-- Delete Confirmation Modal --}}
    @can('delete', $newsPost)
        <flux:modal name="delete-news-post" wire:model.self="showDeleteModal">
            <form wire:submit="delete">
                <flux:heading>{{ __('Eliminar Noticia') }}</flux:heading>
                <flux:text>
                    {{ __('¿Estás seguro de que deseas eliminar esta noticia?') }}
                    <br>
                    <strong>{{ $newsPost->title }}</strong>
                </flux:text>
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará la noticia como eliminada, pero no se eliminará permanentemente. Podrás restaurarla más tarde.') }}
                </flux:text>
                <div class="flex justify-end gap-2 mt-6">
                    <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                        {{ __('common.actions.cancel') }}
                    </flux:button>
                    <flux:button 
                        type="submit" 
                        variant="danger"
                        wire:loading.attr="disabled"
                        wire:target="delete"
                    >
                        <span wire:loading.remove wire:target="delete">
                            {{ __('common.actions.delete') }}
                        </span>
                        <span wire:loading wire:target="delete">
                            {{ __('Eliminando...') }}
                        </span>
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endcan

    {{-- Create Tag Modal --}}
    @can('create', \App\Models\NewsTag::class)
        <flux:modal name="create-tag" wire:model.self="showCreateTagModal">
            <form wire:submit="createTag">
                <flux:heading>{{ __('Crear Nueva Etiqueta') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Crea una nueva etiqueta para usar en esta y otras noticias') }}
                </flux:text>

                <div class="mt-6 space-y-4">
                    {{-- Tag Name --}}
                    <flux:field>
                        <flux:label>
                            {{ __('Nombre') }} <span class="text-red-500">*</span>
                        </flux:label>
                        <flux:input 
                            wire:model.live.blur="newTagName" 
                            placeholder="{{ __('Ej: Movilidad Internacional') }}"
                            required
                            autofocus
                        />
                        <flux:description>{{ __('Nombre de la etiqueta') }}</flux:description>
                        @error('newTagName')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>

                    {{-- Tag Slug --}}
                    <flux:field>
                        <flux:label>{{ __('Slug') }}</flux:label>
                        <flux:input 
                            wire:model.blur="newTagSlug" 
                            placeholder="{{ __('Se genera automáticamente desde el nombre') }}"
                        />
                        <flux:description>{{ __('URL amigable (se genera automáticamente si se deja vacío)') }}</flux:description>
                        @error('newTagSlug')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <flux:button type="button" wire:click="$set('showCreateTagModal', false)" variant="ghost">
                        {{ __('common.actions.cancel') }}
                    </flux:button>
                    <flux:button 
                        type="submit" 
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="createTag"
                    >
                        <span wire:loading.remove wire:target="createTag">
                            {{ __('Crear Etiqueta') }}
                        </span>
                        <span wire:loading wire:target="createTag">
                            {{ __('Creando...') }}
                        </span>
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endcan

    {{-- Toast Notifications --}}
    <x-ui.toast event="news-post-updated" variant="success" />
    <x-ui.toast event="news-post-deleted" variant="success" />
    <x-ui.toast event="tag-created" variant="success" />
</div>
