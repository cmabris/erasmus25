<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Documentos') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona los documentos disponibles en el sistema') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.documents.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Documento') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('common.nav.documents'), 'icon' => 'document'],
            ]"
        />
    </div>

    {{-- Filters and Search --}}
    <div class="mb-6 animate-fade-in" style="animation-delay: 0.1s;">
        <x-ui.card>
            <div class="space-y-4">
                {{-- First Row: Search and Reset --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    {{-- Search --}}
                    <div class="flex-1">
                        <x-ui.search-input 
                            wire:model.live.debounce.300ms="search"
                            :placeholder="__('Buscar por título, descripción o slug...')"
                        />
                    </div>

                    {{-- Reset Filters --}}
                    <div>
                        <flux:button 
                            variant="ghost" 
                            wire:click="resetFilters"
                            icon="arrow-path"
                        >
                            {{ __('common.actions.reset') }}
                        </flux:button>
                    </div>
                </div>

                {{-- Second Row: Filters --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    {{-- Category Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Categoría') }}</flux:label>
                        <select wire:model.live="categoryId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todas') }}</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Program Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Programa') }}</flux:label>
                        <select wire:model.live="programId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Academic Year Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Año Académico') }}</flux:label>
                        <select wire:model.live="academicYearId" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}">{{ $academicYear->year }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Document Type Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Tipo') }}</flux:label>
                        <select wire:model.live="documentType" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->getDocumentTypeOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Active Status Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Estado') }}</flux:label>
                        <select wire:model.live="isActive" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            <option value="1">{{ __('Activo') }}</option>
                            <option value="0">{{ __('Inactivo') }}</option>
                        </select>
                    </flux:field>

                    {{-- Show Deleted Filter --}}
                    @if($this->canViewDeleted())
                        <flux:field>
                            <flux:label>{{ __('Mostrar eliminados') }}</flux:label>
                            <select wire:model.live="showDeleted" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="0">{{ __('No') }}</option>
                                <option value="1">{{ __('Sí') }}</option>
                            </select>
                        </flux:field>
                    @endif
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay wire:target="search,sortBy,updatedCategoryId,updatedProgramId,updatedAcademicYearId,updatedDocumentType,updatedIsActive,updatedShowDeleted" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Documents Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedCategoryId,updatedProgramId,updatedAcademicYearId,updatedDocumentType,updatedIsActive,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->documents->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay documentos')"
                :description="__('No se encontraron documentos que coincidan con los filtros aplicados.')"
                icon="document"
                :action="__('Crear Documento')"
                :actionHref="route('admin.documents.create')"
                actionIcon="plus"
            />
        @else
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Archivo') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('title')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Título') }}
                                        @if($sortField === 'title')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4"
                                                variant="outline"
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Categoría') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Tipo') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Programa') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Año Académico') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Estado') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Descargas') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('created_at')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Fecha') }}
                                        @if($sortField === 'created_at')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4"
                                                variant="outline"
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($this->documents as $document)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @php
                                            $file = $document->getFirstMedia('file');
                                        @endphp
                                        @if($file)
                                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                                @if(str_starts_with($file->mime_type, 'image/'))
                                                    <img 
                                                        src="{{ $file->getUrl() }}" 
                                                        alt="{{ $document->title }}"
                                                        class="h-12 w-12 rounded-lg object-cover"
                                                        loading="lazy"
                                                    />
                                                @else
                                                    <flux:icon name="document" class="[:where(&)]:size-6 text-zinc-400" variant="outline" />
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                                <flux:icon name="document" class="[:where(&)]:size-6 text-zinc-400" variant="outline" />
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $document->title }}
                                            </div>
                                            @if($document->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                        @if($document->description)
                                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                {{ \Illuminate\Support\Str::limit($document->description, 60) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $document->category->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-ui.badge variant="info" size="sm">
                                            {{ $this->getDocumentTypeOptions()[$document->document_type] ?? $document->document_type }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $document->program->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $document->academicYear->year ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-ui.badge :variant="$document->is_active ? 'success' : 'neutral'" size="sm">
                                            {{ $document->is_active ? __('Activo') : __('Inactivo') }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ number_format($document->download_count, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $document->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.documents.show', $document) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles del documento')"
                                            />

                                            @if(!$document->trashed())
                                                {{-- Edit --}}
                                                @can('update', $document)
                                                    <flux:button 
                                                        href="{{ route('admin.documents.edit', $document) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar documento')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @if($this->canDeleteDocument($document))
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $document->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('common.actions.delete')"
                                                        :tooltip="__('Eliminar documento')"
                                                    />
                                                @else
                                                    <flux:button 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        disabled
                                                        :tooltip="__('No se puede eliminar porque tiene consentimientos de medios asociados')"
                                                    />
                                                @endif
                                            @else
                                                {{-- Restore --}}
                                                @can('restore', $document)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $document->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('common.actions.restore')"
                                                        :tooltip="__('Restaurar documento')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $document)
                                                    @if($this->canDeleteDocument($document))
                                                        <flux:button 
                                                            wire:click="confirmForceDelete({{ $document->id }})" 
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="trash"
                                                            :label="__('common.actions.force_delete')"
                                                            :tooltip="__('Eliminar permanentemente')"
                                                        />
                                                    @else
                                                        <flux:button 
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="trash"
                                                            disabled
                                                            :tooltip="__('No se puede eliminar permanentemente porque tiene consentimientos de medios asociados')"
                                                        />
                                                    @endif
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-6 border-t border-zinc-200 px-4 py-4 dark:border-zinc-700 sm:px-6">
                    {{ $this->documents->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" name="delete-document">
        <form wire:submit="delete">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Documento') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Está seguro de que desea eliminar este documento? Esta acción puede revertirse.') }}
                </p>
            </div>
            <div class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <flux:button 
                    type="button"
                    wire:click="$set('showDeleteModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="danger"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.delete') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Restore Confirmation Modal --}}
    <flux:modal wire:model="showRestoreModal" name="restore-document">
        <form wire:submit="restore">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Restaurar Documento') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Está seguro de que desea restaurar este documento?') }}
                </p>
            </div>
            <div class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <flux:button 
                    type="button"
                    wire:click="$set('showRestoreModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="primary"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.restore') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Confirmation Modal --}}
    <flux:modal wire:model="showForceDeleteModal" name="force-delete-document">
        <form wire:submit="forceDelete">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('Eliminar Permanentemente') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('¿Está seguro de que desea eliminar permanentemente este documento? Esta acción no se puede deshacer.') }}
                </p>
            </div>
            <div class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <flux:button 
                    type="button"
                    wire:click="$set('showForceDeleteModal', false)"
                    variant="ghost"
                >
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit"
                    variant="danger"
                    wire:loading.attr="disabled"
                >
                    {{ __('common.actions.force_delete') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
