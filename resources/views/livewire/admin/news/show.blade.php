<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $newsPost->title }}
                    </h1>
                    @if($newsPost->trashed())
                        <x-ui.badge variant="danger" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @else
                        <x-ui.badge :variant="$this->getStatusColor($newsPost->status)" size="lg">
                            {{ ucfirst(str_replace('_', ' ', $newsPost->status)) }}
                        </x-ui.badge>
                    @endif
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($newsPost->program)
                        {{ __('Programa') }}: <strong>{{ $newsPost->program->name }}</strong>
                        ·
                    @endif
                    {{ __('Año Académico') }}: <strong>{{ $newsPost->academicYear->year ?? '-' }}</strong>
                    @if($newsPost->published_at)
                        · {{ __('Publicada') }}: <strong>{{ $newsPost->published_at->format('d/m/Y') }}</strong>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$newsPost->trashed())
                    @if($this->canPublish())
                        @if($newsPost->status === 'publicado')
                            <flux:button 
                                wire:click="unpublish"
                                variant="ghost"
                                icon="lock-closed"
                                wire:loading.attr="disabled"
                                wire:target="unpublish"
                            >
                                <span wire:loading.remove wire:target="unpublish">
                                    {{ __('Despublicar') }}
                                </span>
                                <span wire:loading wire:target="unpublish">
                                    {{ __('Despublicando...') }}
                                </span>
                            </flux:button>
                        @else
                            <flux:button 
                                wire:click="publish"
                                variant="ghost"
                                icon="paper-airplane"
                                wire:loading.attr="disabled"
                                wire:target="publish"
                            >
                                <span wire:loading.remove wire:target="publish">
                                    {{ __('Publicar') }}
                                </span>
                                <span wire:loading wire:target="publish">
                                    {{ __('Publicando...') }}
                                </span>
                            </flux:button>
                        @endif
                    @endif
                    @can('update', $newsPost)
                        <flux:button 
                            href="{{ route('admin.news.edit', $newsPost) }}" 
                            variant="primary"
                            wire:navigate
                            icon="pencil"
                        >
                            {{ __('common.actions.edit') }}
                        </flux:button>
                    @endcan
                @endif
                <flux:button 
                    href="{{ route('admin.news.index') }}" 
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
                ['label' => __('common.nav.news'), 'href' => route('admin.news.index'), 'icon' => 'newspaper'],
                ['label' => $newsPost->title, 'icon' => 'newspaper'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Featured Image --}}
            @if($this->hasFeaturedImage())
                <x-ui.card>
                    <div class="space-y-4">
                        @php
                            // Intentar obtener large, si no existe usar medium, si no existe usar original
                            $largeImageUrl = $this->getFeaturedImageUrl('large');
                            $mediumImageUrl = $this->getFeaturedImageUrl('medium');
                            $originalImageUrl = $this->getFeaturedImageUrl();
                            $imageUrl = $largeImageUrl ?? $mediumImageUrl ?? $originalImageUrl;
                            $media = $newsPost->getFirstMedia('featured');
                        @endphp
                        @if($imageUrl)
                            <img 
                                src="{{ $imageUrl }}" 
                                alt="{{ $newsPost->title }}"
                                class="w-full rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                loading="lazy"
                            />
                        @endif
                        @if($media)
                            <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="photo" class="[:where(&)]:size-4" variant="outline" />
                                    <span>{{ $media->name }}</span>
                                </div>
                                <span>{{ number_format($media->size / 1024, 2) }} KB</span>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @endif

            {{-- Excerpt --}}
            @if($newsPost->excerpt)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Extracto') }}</flux:heading>
                        <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                            {{ $newsPost->excerpt }}
                        </p>
                    </div>
                </x-ui.card>
            @endif

            {{-- Content --}}
            @if($newsPost->content)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Contenido') }}</flux:heading>
                        <div class="prose prose-sm max-w-none dark:prose-invert prose-headings:font-bold prose-p:text-zinc-700 dark:prose-p:text-zinc-300 prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-strong:font-bold prose-em:italic prose-ul:list-disc prose-ol:list-decimal prose-img:rounded-lg prose-img:my-4 prose-blockquote:border-l-4 prose-blockquote:border-zinc-300 prose-blockquote:pl-4 prose-blockquote:italic dark:prose-blockquote:border-zinc-600 prose-hr:my-4 prose-table:w-full prose-table:my-4 prose-th:bg-zinc-100 dark:prose-th:bg-zinc-800 prose-th:font-bold">
                            {!! $newsPost->content !!}
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Mobility Information --}}
            @if($newsPost->country || $newsPost->city || $newsPost->host_entity || $newsPost->mobility_type || $newsPost->mobility_category)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Información de Movilidad') }}</flux:heading>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($newsPost->country)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('País') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $newsPost->country }}</p>
                                </div>
                            @endif
                            @if($newsPost->city)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Ciudad') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $newsPost->city }}</p>
                                </div>
                            @endif
                            @if($newsPost->host_entity)
                                <div class="sm:col-span-2">
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Entidad de Acogida') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $newsPost->host_entity }}</p>
                                </div>
                            @endif
                            @if($newsPost->mobility_type)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tipo de Movilidad') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $newsPost->mobility_type === 'alumnado' ? __('Alumnado') : __('Personal') }}
                                    </p>
                                </div>
                            @endif
                            @if($newsPost->mobility_category)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Categoría de Movilidad') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ match($newsPost->mobility_category) {
                                            'FCT' => __('FCT (Formación en Centros de Trabajo)'),
                                            'job_shadowing' => __('Job Shadowing'),
                                            'intercambio' => __('Intercambio'),
                                            'curso' => __('Curso'),
                                            'otro' => __('Otro'),
                                            default => $newsPost->mobility_category,
                                        } }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Tags --}}
            @if($newsPost->tags->isNotEmpty())
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Etiquetas') }}</flux:heading>
                        <div class="flex flex-wrap gap-2">
                            @foreach($newsPost->tags as $tag)
                                <x-ui.badge variant="info" size="md">
                                    {{ $tag->name }}
                                </x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Basic Information --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información Básica') }}</flux:heading>
                    </div>

                    <div class="space-y-3 text-sm">
                        @if($newsPost->program)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Programa') }}</p>
                                <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $newsPost->program->name }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Año Académico') }}</p>
                            <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $newsPost->academicYear->year ?? '-' }}</p>
                        </div>

                        @if($newsPost->slug)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Slug') }}</p>
                                <p class="mt-1 font-mono text-zinc-900 dark:text-white">{{ $newsPost->slug }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Estado') }}</p>
                            <div class="mt-1">
                                <x-ui.badge :variant="$this->getStatusColor($newsPost->status)" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $newsPost->status)) }}
                                </x-ui.badge>
                            </div>
                        </div>

                        @if($newsPost->published_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fecha de Publicación') }}</p>
                                <p class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                    {{ $newsPost->published_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Author and Dates --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Autoría y Fechas') }}</flux:heading>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Autor') }}</p>
                            <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $newsPost->author->name ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Creado') }}</p>
                            <p class="mt-1 text-zinc-900 dark:text-white">{{ $newsPost->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                            <p class="mt-1 text-zinc-900 dark:text-white">{{ $newsPost->updated_at->format('d/m/Y H:i') }}</p>
                        </div>

                        @if($newsPost->reviewer)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Revisor') }}</p>
                                <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $newsPost->reviewer->name }}</p>
                            </div>
                        @endif

                        @if($newsPost->reviewed_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Revisado') }}</p>
                                <p class="mt-1 text-zinc-900 dark:text-white">{{ $newsPost->reviewed_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Actions --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Acciones') }}</flux:heading>
                    </div>

                    <div class="flex flex-col gap-2">
                        @if(!$newsPost->trashed())
                            @can('update', $newsPost)
                                <flux:button 
                                    href="{{ route('admin.news.edit', $newsPost) }}" 
                                    variant="primary"
                                    wire:navigate
                                    icon="pencil"
                                    class="w-full"
                                >
                                    {{ __('common.actions.edit') }}
                                </flux:button>
                            @endcan

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
                        @else
                            @can('restore', $newsPost)
                                <flux:button 
                                    type="button"
                                    wire:click="$set('showRestoreModal', true)"
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('common.actions.restore') }}
                                </flux:button>
                            @endcan

                            @can('forceDelete', $newsPost)
                                <flux:button 
                                    type="button"
                                    wire:click="$set('showForceDeleteModal', true)"
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                >
                                    {{ __('common.actions.permanently_delete') }}
                                </flux:button>
                            @endcan
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>
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

    {{-- Restore Confirmation Modal --}}
    @can('restore', $newsPost)
        <flux:modal name="restore-news-post" wire:model.self="showRestoreModal">
            <form wire:submit="restore">
                <flux:heading>{{ __('Restaurar Noticia') }}</flux:heading>
                <flux:text>
                    {{ __('¿Estás seguro de que deseas restaurar esta noticia?') }}
                    <br>
                    <strong>{{ $newsPost->title }}</strong>
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
                            {{ __('common.actions.restore') }}
                        </span>
                        <span wire:loading wire:target="restore">
                            {{ __('Restaurando...') }}
                        </span>
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endcan

    {{-- Force Delete Confirmation Modal --}}
    @can('forceDelete', $newsPost)
        <flux:modal name="force-delete-news-post" wire:model.self="showForceDeleteModal">
            <form wire:submit="forceDelete">
                <flux:heading>{{ __('Eliminar Permanentemente') }}</flux:heading>
                <flux:text>
                    {{ __('¿Estás seguro de que deseas eliminar permanentemente esta noticia?') }}
                    <br>
                    <strong>{{ $newsPost->title }}</strong>
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
                            {{ __('common.actions.permanently_delete') }}
                        </span>
                        <span wire:loading wire:target="forceDelete">
                            {{ __('Eliminando...') }}
                        </span>
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endcan

    {{-- Toast Notifications --}}
    <x-ui.toast event="news-post-published" variant="success" />
    <x-ui.toast event="news-post-unpublished" variant="success" />
    <x-ui.toast event="news-post-deleted" variant="success" />
    <x-ui.toast event="news-post-restored" variant="success" />
    <x-ui.toast event="news-post-force-deleted" variant="warning" />
</div>
