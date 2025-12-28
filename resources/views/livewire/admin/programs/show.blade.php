<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $program->name }}
                    </h1>
                    @if($program->trashed())
                        <x-ui.badge color="red" size="lg">
                            {{ __('Eliminado') }}
                        </x-ui.badge>
                    @elseif($program->is_active)
                        <x-ui.badge color="green" size="lg">
                            {{ __('Activo') }}
                        </x-ui.badge>
                    @else
                        <x-ui.badge color="gray" size="lg">
                            {{ __('Inactivo') }}
                        </x-ui.badge>
                    @endif
                </div>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Código') }}: <strong>{{ $program->code }}</strong>
                    @if($program->slug)
                        · {{ __('Slug') }}: <code class="text-xs">{{ $program->slug }}</code>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $program)
                    <flux:button 
                        href="{{ route('admin.programs.edit', $program) }}" 
                        variant="primary"
                        wire:navigate
                        icon="pencil"
                    >
                        {{ __('common.actions.edit') }}
                    </flux:button>
                @endcan
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
                ['label' => $program->name, 'icon' => 'academic-cap'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Image --}}
            @if($this->hasImage())
                <x-ui.card>
                    <img 
                        src="{{ $this->getImageUrl('large') }}" 
                        alt="{{ $program->name }}"
                        class="w-full rounded-lg object-cover"
                    />
                </x-ui.card>
            @endif

            {{-- Description --}}
            @if($program->description)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Descripción') }}</flux:heading>
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                {{ $program->description }}
                            </p>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Statistics --}}
            <x-ui.card>
                <div>
                    <flux:heading size="md" class="mb-4">{{ __('Estadísticas') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-erasmus-100 p-2 dark:bg-erasmus-900/30">
                                    <flux:icon name="megaphone" class="[:where(&)]:size-5 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Convocatorias') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_calls'] }}
                                    </p>
                                    @if($this->statistics['active_calls'] > 0)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $this->statistics['active_calls'] }} {{ __('activas') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                    <flux:icon name="newspaper" class="[:where(&)]:size-5 text-blue-600 dark:text-blue-400" variant="outline" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Noticias') }}</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                                        {{ $this->statistics['total_news'] }}
                                    </p>
                                    @if($this->statistics['published_news'] > 0)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $this->statistics['published_news'] }} {{ __('publicadas') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Translations --}}
            @if(count($this->availableTranslations) > 0)
                <x-ui.card>
                    <div>
                        <flux:heading size="md" class="mb-4">{{ __('Traducciones Disponibles') }}</flux:heading>
                        <div class="space-y-4">
                            @foreach($this->availableTranslations as $translation)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="mb-2 flex items-center gap-2">
                                        <flux:heading size="xs">{{ $translation['language']->name }} ({{ strtoupper($translation['language']->code) }})</flux:heading>
                                        @if($translation['language']->code === getCurrentLanguageCode())
                                            <x-ui.badge color="blue" size="sm">{{ __('Idioma actual') }}</x-ui.badge>
                                        @endif
                                    </div>
                                    
                                    @if($translation['name'])
                                        <div class="mb-2">
                                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Nombre') }}:</p>
                                            <p class="text-zinc-900 dark:text-white">{{ $translation['name'] }}</p>
                                        </div>
                                    @endif
                                    
                                    @if($translation['description'])
                                        <div>
                                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Descripción') }}:</p>
                                            <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-line">{{ $translation['description'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Program Details --}}
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Información del Programa') }}</flux:heading>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Código') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $program->code }}</p>
                        </div>

                        @if($program->slug)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Slug') }}</p>
                                <p class="mt-1 text-sm font-mono text-zinc-900 dark:text-white">{{ $program->slug }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Orden') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $program->order }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Estado') }}</p>
                            <div class="mt-1">
                                @if($program->trashed())
                                    <x-ui.badge color="red">{{ __('Eliminado') }}</x-ui.badge>
                                @elseif($program->is_active)
                                    <x-ui.badge color="green">{{ __('Activo') }}</x-ui.badge>
                                @else
                                    <x-ui.badge color="gray">{{ __('Inactivo') }}</x-ui.badge>
                                @endif
                            </div>
                        </div>

                        @if($program->created_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Creado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $program->created_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if($program->updated_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actualizado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $program->updated_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        @endif

                        @if($program->deleted_at)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Eliminado') }}</p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $program->deleted_at->translatedFormat('d M Y') }}
                                </p>
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
                        @can('update', $program)
                            @if($program->trashed())
                                <flux:button 
                                    wire:click="$set('showRestoreModal', true)"
                                    variant="primary"
                                    icon="arrow-path"
                                    class="w-full"
                                >
                                    {{ __('common.actions.restore') }}
                                </flux:button>
                            @else
                                <flux:button 
                                    wire:click="toggleActive"
                                    variant="{{ $program->is_active ? 'danger' : 'primary' }}"
                                    icon="{{ $program->is_active ? 'x-circle' : 'check-circle' }}"
                                    class="w-full"
                                >
                                    {{ $program->is_active ? __('Desactivar') : __('Activar') }}
                                </flux:button>
                            @endif
                        @endcan

                        @can('delete', $program)
                            @if($program->trashed())
                                @can('forceDelete', $program)
                                    <flux:button 
                                        wire:click="$set('showForceDeleteModal', true)"
                                        variant="danger"
                                        icon="trash"
                                        class="w-full"
                                        :disabled="!$this->canDelete()"
                                        :title="!$this->canDelete() ? __('No se puede eliminar porque tiene relaciones activas') : ''"
                                    >
                                        {{ __('common.actions.permanently_delete') }}
                                    </flux:button>
                                @endcan
                            @else
                                <flux:button 
                                    wire:click="$set('showDeleteModal', true)"
                                    variant="danger"
                                    icon="trash"
                                    class="w-full"
                                    :disabled="!$this->canDelete()"
                                    :title="!$this->canDelete() ? __('No se puede eliminar porque tiene relaciones activas') : ''"
                                >
                                    {{ __('common.actions.delete') }}
                                </flux:button>
                            @endif
                        @endcan
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Delete Modal --}}
    <flux:modal wire:model.self="showDeleteModal" name="delete-program">
        <form wire:submit="delete" class="space-y-4">
            <flux:heading size="lg">{{ __('Eliminar Programa') }}</flux:heading>

            <flux:text class="mt-4">
                {{ __('¿Estás seguro de que deseas eliminar este programa?') }}
                @if($this->hasRelationships())
                    <br><br>
                    <span class="text-red-600 dark:text-red-400 font-medium">
                        {{ __('No se puede eliminar este programa porque tiene relaciones activas (convocatorias o noticias).') }}
                    </span>
                @endif
            </flux:text>

            @if(!$this->hasRelationships())
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Esta acción marcará el programa como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.') }}
                </flux:text>
            @endif

            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                @if($this->canDelete())
                    <flux:button type="submit" variant="danger">
                        {{ __('common.actions.delete') }}
                    </flux:button>
                @endif
            </div>
        </form>
    </flux:modal>

    {{-- Restore Modal --}}
    <flux:modal wire:model.self="showRestoreModal" name="restore-program">
        <form wire:submit="restore" class="space-y-4">
            <flux:heading size="lg">{{ __('Restaurar Programa') }}</flux:heading>

            <flux:text class="mt-4">
                {{ __('¿Estás seguro de que deseas restaurar este programa?') }}
            </flux:text>

            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="$set('showRestoreModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('common.actions.restore') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Force Delete Modal --}}
    <flux:modal wire:model.self="showForceDeleteModal" name="force-delete-program">
        <form wire:submit="forceDelete" class="space-y-4">
            <flux:heading size="lg">{{ __('Eliminar Permanentemente') }}</flux:heading>

            <flux:text class="mt-4">
                {{ __('¿Estás seguro de que deseas eliminar permanentemente este programa?') }}
            </flux:text>

            <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ __('Esta acción no se puede deshacer. El programa se eliminará permanentemente de la base de datos.') }}
            </flux:text>

            @if($program->calls()->exists() || $program->newsPosts()->exists())
                <flux:callout variant="danger" class="mt-4">
                    <flux:text>
                        {{ __('No se puede eliminar permanentemente este programa porque tiene relaciones activas.') }}
                    </flux:text>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="$set('showForceDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="danger"
                    :disabled="$program->calls()->exists() || $program->newsPosts()->exists()"
                >
                    {{ __('common.actions.permanently_delete') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast Notifications --}}
    <x-ui.toast event="program-updated" variant="success" />
    <x-ui.toast event="program-deleted" variant="success" />
    <x-ui.toast event="program-restored" variant="success" />
    <x-ui.toast event="program-force-deleted" variant="warning" />
    <x-ui.toast event="program-force-delete-error" variant="error" />
    <x-ui.toast event="program-delete-error" variant="error" />
</div>
