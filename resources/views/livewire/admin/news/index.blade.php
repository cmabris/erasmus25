<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Noticias') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Gestiona las noticias y publicaciones del sistema') }}
                </p>
            </div>
            @if($this->canCreate())
                <flux:button 
                    href="{{ route('admin.news.create') }}" 
                    variant="primary"
                    wire:navigate
                    icon="plus"
                >
                    {{ __('Crear Noticia') }}
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Noticias'), 'icon' => 'newspaper'],
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
                            :placeholder="__('Buscar por título, extracto o slug...')"
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                    {{-- Program Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Programa') }}</flux:label>
                        <select wire:model.live="filterProgram" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Academic Year Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Año Académico') }}</flux:label>
                        <select wire:model.live="filterAcademicYear" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($this->academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}">{{ $academicYear->year }}</option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- Status Filter --}}
                    <flux:field>
                        <flux:label>{{ __('Estado') }}</flux:label>
                        <select wire:model.live="filterStatus" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            <option value="">{{ __('Todos') }}</option>
                            <option value="borrador">{{ __('Borrador') }}</option>
                            <option value="en_revision">{{ __('En Revisión') }}</option>
                            <option value="publicado">{{ __('Publicado') }}</option>
                            <option value="archivado">{{ __('Archivado') }}</option>
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
    <div wire:loading.delay wire:target="search,sortBy,updatedFilterProgram,updatedFilterAcademicYear,updatedFilterStatus,updatedShowDeleted" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- News Posts Table --}}
    <div wire:loading.remove.delay wire:target="search,sortBy,updatedFilterProgram,updatedFilterAcademicYear,updatedFilterStatus,updatedShowDeleted" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->newsPosts->isEmpty())
            <x-ui.empty-state 
                :title="__('No hay noticias')"
                :description="__('No se encontraron noticias que coincidan con los filtros aplicados.')"
                icon="newspaper"
                :action="__('Crear Noticia')"
                :actionHref="route('admin.news.create')"
                actionIcon="plus"
            />
        @else
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Imagen') }}
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
                                    {{ __('Programa') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Año Académico') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('status')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Estado') }}
                                        @if($sortField === 'status')
                                            <flux:icon 
                                                :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" 
                                                class="[:where(&)]:size-4" 
                                                variant="outline" 
                                            />
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Etiquetas') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Autor') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    <button 
                                        wire:click="sortBy('published_at')" 
                                        class="flex items-center gap-2 hover:text-erasmus-600 dark:hover:text-erasmus-400"
                                    >
                                        {{ __('Publicación') }}
                                        @if($sortField === 'published_at')
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
                            @foreach($this->newsPosts as $newsPost)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @php
                                            // Intentar obtener thumbnail, si no existe usar imagen original, si no hay imagen mostrar placeholder
                                            $featuredImage = $newsPost->getFirstMediaUrl('featured', 'thumbnail') 
                                                ?? $newsPost->getFirstMediaUrl('featured') 
                                                ?? null;
                                        @endphp
                                        @if($featuredImage)
                                            <img 
                                                src="{{ $featuredImage }}" 
                                                alt="{{ $newsPost->title }}"
                                                class="h-12 w-12 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                                loading="lazy"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                            />
                                            <div class="hidden h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                                <flux:icon name="photo" class="[:where(&)]:size-6 text-zinc-400" variant="outline" />
                                            </div>
                                        @else
                                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                                <flux:icon name="photo" class="[:where(&)]:size-6 text-zinc-400" variant="outline" />
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $newsPost->title }}
                                            </div>
                                            @if($newsPost->trashed())
                                                <x-ui.badge variant="danger" size="sm">
                                                    {{ __('Eliminado') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                        @if($newsPost->excerpt)
                                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                {{ \Illuminate\Support\Str::limit($newsPost->excerpt, 60) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $newsPost->program->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $newsPost->academicYear->year ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-ui.badge :variant="$this->getStatusColor($newsPost->status)" size="sm">
                                            {{ ucfirst(str_replace('_', ' ', $newsPost->status)) }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($newsPost->tags->take(3) as $tag)
                                                <x-ui.badge variant="info" size="sm">
                                                    {{ $tag->name }}
                                                </x-ui.badge>
                                            @empty
                                                <span class="text-xs text-zinc-400">-</span>
                                            @endforelse
                                            @if($newsPost->tags_count > 3)
                                                <x-ui.badge variant="neutral" size="sm">
                                                    +{{ $newsPost->tags_count - 3 }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $newsPost->author->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($newsPost->published_at)
                                            <x-ui.badge variant="success" size="sm">
                                                {{ __('Publicada') }}
                                            </x-ui.badge>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $newsPost->published_at->format('d/m/Y') }}
                                            </p>
                                        @else
                                            <span class="text-sm text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <flux:button 
                                                href="{{ route('admin.news.show', $newsPost) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                wire:navigate
                                                icon="eye"
                                                :label="__('common.actions.view')"
                                                :tooltip="__('Ver detalles de la noticia')"
                                            />

                                            @if(!$newsPost->trashed())
                                                {{-- Publish/Unpublish --}}
                                                @if($this->canPublishNewsPost($newsPost))
                                                    @if($newsPost->status === 'publicado')
                                                        <flux:button 
                                                            wire:click="unpublish({{ $newsPost->id }})"
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="lock-closed"
                                                            :label="__('Despublicar')"
                                                            :tooltip="__('Despublicar noticia')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="unpublish({{ $newsPost->id }})"
                                                        />
                                                    @else
                                                        <flux:button 
                                                            wire:click="publish({{ $newsPost->id }})"
                                                            variant="ghost" 
                                                            size="sm"
                                                            icon="paper-airplane"
                                                            :label="__('Publicar')"
                                                            :tooltip="__('Publicar noticia')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="publish({{ $newsPost->id }})"
                                                        />
                                                    @endif
                                                @endif

                                                {{-- Edit --}}
                                                @can('update', $newsPost)
                                                    <flux:button 
                                                        href="{{ route('admin.news.edit', $newsPost) }}" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        wire:navigate
                                                        icon="pencil"
                                                        :label="__('common.actions.edit')"
                                                        :tooltip="__('Editar noticia')"
                                                    />
                                                @endcan

                                                {{-- Delete --}}
                                                @if($this->canDeleteNewsPost($newsPost))
                                                    <flux:button 
                                                        wire:click="confirmDelete({{ $newsPost->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('common.actions.delete')"
                                                        :tooltip="__('Eliminar noticia')"
                                                    />
                                                @endif
                                            @else
                                                {{-- Restore --}}
                                                @can('restore', $newsPost)
                                                    <flux:button 
                                                        wire:click="confirmRestore({{ $newsPost->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="arrow-path"
                                                        :label="__('Restaurar')"
                                                        :tooltip="__('Restaurar noticia eliminada')"
                                                    />
                                                @endcan

                                                {{-- Force Delete --}}
                                                @can('forceDelete', $newsPost)
                                                    <flux:button 
                                                        wire:click="confirmForceDelete({{ $newsPost->id }})" 
                                                        variant="ghost" 
                                                        size="sm"
                                                        icon="trash"
                                                        :label="__('Eliminar permanentemente')"
                                                        :tooltip="__('Eliminar permanentemente del sistema')"
                                                    />
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
                <div class="mt-6">
                    {{ $this->newsPosts->links() }}
                </div>
            </x-ui.card>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-news-post" wire:model.self="showDeleteModal">
        <form wire:submit="delete">
            <flux:heading>{{ __('Eliminar Noticia') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar esta noticia?') }}
                @if($newsPostToDelete)
                    @php
                        $newsPost = \App\Models\NewsPost::find($newsPostToDelete);
                    @endphp
                    <br>
                    <strong>{{ $newsPost?->title }}</strong>
                @endif
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

    {{-- Restore Confirmation Modal --}}
    <flux:modal name="restore-news-post" wire:model.self="showRestoreModal">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading>{{ __('Restaurar Noticia') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas restaurar esta noticia?') }}
                @if($newsPostToRestore)
                    <br>
                    <strong>{{ \App\Models\NewsPost::onlyTrashed()->find($newsPostToRestore)?->title }}</strong>
                @endif
            </flux:text>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showRestoreModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="primary"
                    wire:loading.attr="disabled"
                    wire:target="restore"
                >
                    <span wire:loading.remove wire:target="restore">
                        {{ __('Restaurar') }}
                    </span>
                    <span wire:loading wire:target="restore">
                        {{ __('Restaurando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Confirmation Modal --}}
    <flux:modal name="force-delete-news-post" wire:model.self="showForceDeleteModal">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
            <flux:text>
                {{ __('¿Estás seguro de que deseas eliminar permanentemente esta noticia?') }}
                @if($newsPostToForceDelete)
                    <br>
                    <strong>{{ \App\Models\NewsPost::onlyTrashed()->find($newsPostToForceDelete)?->title }}</strong>
                @endif
            </flux:text>
            <flux:callout variant="danger" class="mt-4">
                <flux:callout.heading>{{ __('⚠️ Acción Irreversible') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Esta acción realizará una eliminación permanente (ForceDelete). La noticia se eliminará completamente de la base de datos y esta acción NO se puede deshacer.') }}
                </flux:callout.text>
            </flux:callout>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="danger"
                    wire:loading.attr="disabled"
                    wire:target="forceDelete"
                >
                    <span wire:loading.remove wire:target="forceDelete">
                        {{ __('Eliminar permanentemente') }}
                    </span>
                    <span wire:loading wire:target="forceDelete">
                        {{ __('Eliminando...') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="news-post-deleted" variant="success" />
    <x-ui.toast event="news-post-restored" variant="success" />
    <x-ui.toast event="news-post-force-deleted" variant="warning" />
    <x-ui.toast event="news-post-published" variant="success" />
    <x-ui.toast event="news-post-unpublished" variant="success" />
</div>
