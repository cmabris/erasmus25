<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Crear Resolución') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Añade una nueva resolución para la convocatoria: :title', ['title' => $call->title]) }}
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.calls.resolutions.index', $call) }}" 
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
                ['label' => __('common.nav.calls'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
                ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'megaphone'],
                ['label' => __('common.nav.resolutions'), 'href' => route('admin.calls.resolutions.index', $call), 'icon' => 'document-check'],
                ['label' => __('Crear'), 'icon' => 'plus'],
            ]"
        />
    </div>

    {{-- Call Info Card --}}
    <div class="mb-6 animate-fade-in" style="animation-delay: 0.05s;">
        <x-ui.card>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Programa') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->program->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Año Académico') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->academicYear->year ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Estado') }}</p>
                    <p class="mt-1">
                        <x-ui.badge variant="info" size="sm">
                            {{ ucfirst(str_replace('_', ' ', $call->status)) }}
                        </x-ui.badge>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total de Resoluciones') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->resolutions()->count() }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Main Form Fields --}}
                <div class="lg:col-span-2 space-y-6">
                    <x-ui.card>
                        <div class="space-y-6">
                            {{-- Call Phase Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Fase') }} <span class="text-red-500">*</span>
                                </flux:label>
                                <select 
                                    wire:model.live.blur="call_phase_id" 
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    required
                                >
                                    <option value="">{{ __('Seleccionar fase') }}</option>
                                    @foreach($this->callPhases as $phase)
                                        <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Selecciona la fase de la convocatoria a la que pertenece esta resolución') }}</flux:description>
                                @error('call_phase_id')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Type Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Tipo de Resolución') }} <span class="text-red-500">*</span>
                                </flux:label>
                                <select 
                                    wire:model.live.blur="type" 
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    required
                                >
                                    @foreach($this->getTypeOptions() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Selecciona el tipo de resolución') }}</flux:description>
                                @error('type')
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
                                    placeholder="Ej: Resolución provisional de la convocatoria"
                                    required
                                    autofocus
                                    maxlength="255"
                                />
                                <flux:description>{{ __('Título descriptivo de la resolución') }}</flux:description>
                                @error('title')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Description Field --}}
                            <flux:field>
                                <flux:label>{{ __('Descripción') }}</flux:label>
                                <flux:textarea 
                                    wire:model.live.blur="description" 
                                    placeholder="Descripción detallada de la resolución..."
                                    rows="4"
                                />
                                <flux:description>{{ __('Descripción opcional de la resolución') }}</flux:description>
                                @error('description')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Evaluation Procedure Field --}}
                            <flux:field>
                                <flux:label>{{ __('Procedimiento de Evaluación') }}</flux:label>
                                <flux:textarea 
                                    wire:model.live.blur="evaluation_procedure" 
                                    placeholder="Descripción del procedimiento de evaluación..."
                                    rows="4"
                                />
                                <flux:description>{{ __('Procedimiento de evaluación utilizado (opcional)') }}</flux:description>
                                @error('evaluation_procedure')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Dates Section --}}
                            <div class="grid gap-4 sm:grid-cols-2">
                                {{-- Official Date Field --}}
                                <flux:field>
                                    <flux:label>
                                        {{ __('Fecha Oficial') }} <span class="text-red-500">*</span>
                                    </flux:label>
                                    <flux:input 
                                        wire:model.live.blur="official_date" 
                                        type="date"
                                        required
                                    />
                                    <flux:description>{{ __('Fecha oficial de la resolución') }}</flux:description>
                                    @error('official_date')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                {{-- Published At Field --}}
                                <flux:field>
                                    <flux:label>{{ __('Fecha de Publicación') }}</flux:label>
                                    <flux:input 
                                        wire:model.live.blur="published_at" 
                                        type="date"
                                    />
                                    <flux:description>{{ __('Fecha de publicación (opcional, dejar vacío para publicar manualmente más tarde)') }}</flux:description>
                                    @error('published_at')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>

                            {{-- PDF Upload Field --}}
                            <flux:field>
                                <flux:label>{{ __('PDF de Resolución') }}</flux:label>
                                
                                <x-filepond::upload 
                                    wire:model="pdfFile"
                                    accepted-file-types="application/pdf"
                                    max-file-size="10MB"
                                    label-idle='Arrastra tu PDF aquí o <span class="filepond--label-action">selecciona</span>'
                                    label-file-type-not-allowed="Solo se permiten archivos PDF"
                                    label-file-size-too-large="El archivo es demasiado grande (máximo 10MB)"
                                    label-file-size-too-small="El archivo es demasiado pequeño"
                                    label-file-loading="Cargando"
                                    label-file-processing="Subiendo"
                                    label-file-processing-complete="Subida completa"
                                    label-file-processing-error="Error durante la subida"
                                    label-tap-to-cancel="Toca para cancelar"
                                    label-tap-to-retry="Toca para reintentar"
                                    label-tap-to-undo="Toca para deshacer"
                                />
                                
                                <flux:description>
                                    {{ __('Formatos aceptados: PDF. Tamaño máximo: 10MB') }}
                                </flux:description>
                                
                                @error('pdfFile')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Call Info Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información de la Convocatoria') }}</flux:heading>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ __('Título') }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $call->title }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ __('Programa') }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $call->program->name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ __('Año Académico') }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-900 dark:text-white">
                                    {{ $call->academicYear->year }}
                                </p>
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
                                    wire:target="save"
                                >
                                    <span wire:loading.remove wire:target="save">
                                        {{ __('common.actions.save') }}
                                    </span>
                                    <span wire:loading wire:target="save">
                                        {{ __('Guardando...') }}
                                    </span>
                                </flux:button>

                                <flux:button 
                                    type="button"
                                    href="{{ route('admin.calls.resolutions.index', $call) }}" 
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
    <x-ui.toast event="resolution-created" variant="success" />
</div>
