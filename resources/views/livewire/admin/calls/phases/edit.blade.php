<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Fase') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información de la fase: :name', ['name' => $this->callPhase->name ?? __('Fase')]) }}
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.calls.phases.index', $call) }}" 
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
                ['label' => __('common.nav.phases'), 'href' => route('admin.calls.phases.index', $call), 'icon' => 'calendar'],
                ['label' => $this->callPhase->name ?? __('Fase'), 'href' => isset($this->callPhase->id) ? route('admin.calls.phases.show', [$call, $this->callPhase]) : route('admin.calls.phases.index', $call), 'icon' => 'list-bullet'],
                ['label' => __('common.actions.edit'), 'icon' => 'pencil'],
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
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total de Fases') }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $call->phases()->count() }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <form wire:submit="update" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Main Form Fields --}}
                <div class="lg:col-span-2 space-y-6">
                    <x-ui.card>
                        <div class="space-y-6">
                            {{-- Phase Type Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Tipo de Fase') }} <span class="text-red-500">*</span>
                                </flux:label>
                                <select 
                                    wire:model.live.blur="phase_type" 
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    required
                                >
                                    @foreach($this->getPhaseTypeOptions() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <flux:description>{{ __('Selecciona el tipo de fase del proceso de selección') }}</flux:description>
                                @error('phase_type')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Name Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Nombre de la Fase') }} <span class="text-red-500">*</span>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="name" 
                                    placeholder="Ej: Publicación de la convocatoria"
                                    required
                                    autofocus
                                    maxlength="255"
                                />
                                <flux:description>{{ __('Nombre descriptivo de la fase') }}</flux:description>
                                @error('name')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Description Field --}}
                            <flux:field>
                                <flux:label>{{ __('Descripción') }}</flux:label>
                                <flux:textarea 
                                    wire:model.live.blur="description" 
                                    placeholder="Descripción detallada de la fase..."
                                    rows="4"
                                />
                                <flux:description>{{ __('Descripción opcional de la fase') }}</flux:description>
                                @error('description')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Dates Section --}}
                            <div class="grid gap-4 sm:grid-cols-2">
                                {{-- Start Date Field --}}
                                <flux:field>
                                    <flux:label>{{ __('Fecha de Inicio') }}</flux:label>
                                    <flux:input 
                                        wire:model.live.blur="start_date" 
                                        type="date"
                                    />
                                    <flux:description>{{ __('Fecha de inicio de la fase (opcional)') }}</flux:description>
                                    @error('start_date')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                {{-- End Date Field --}}
                                <flux:field>
                                    <flux:label>{{ __('Fecha de Fin') }}</flux:label>
                                    <flux:input 
                                        wire:model.live.blur="end_date" 
                                        type="date"
                                        :min="$start_date ? $start_date : null"
                                    />
                                    <flux:description>{{ __('Fecha de fin de la fase (opcional, debe ser posterior a la fecha de inicio)') }}</flux:description>
                                    @if($start_date && $end_date && $end_date <= $start_date)
                                        <flux:error>{{ __('La fecha de fin debe ser posterior a la fecha de inicio.') }}</flux:error>
                                    @endif
                                    @error('end_date')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Relations Info Card --}}
                    @php
                        $resolutionsCount = $this->callPhase->resolutions()->count();
                        $hasRelations = $resolutionsCount > 0;
                    @endphp
                    @if($hasRelations)
                        <x-ui.card>
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="sm">{{ __('Relaciones Existentes') }}</flux:heading>
                                    <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Esta fase tiene las siguientes relaciones activas:') }}
                                    </flux:text>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $resolutionsCount }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Resoluciones') }}
                                        </div>
                                    </div>
                                </div>
                                <flux:callout variant="info" class="mt-2">
                                    <flux:callout.text>
                                        {{ __('Tenga en cuenta que cambiar la fase puede afectar a estas relaciones.') }}
                                    </flux:callout.text>
                                </flux:callout>
                            </div>
                        </x-ui.card>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Settings Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Configuración') }}</flux:heading>
                            </div>

                            {{-- Order Field --}}
                            <flux:field>
                                <flux:label>{{ __('Orden') }}</flux:label>
                                <flux:input 
                                    wire:model.live.blur="order" 
                                    type="number"
                                    min="0"
                                />
                                <flux:description>{{ __('Orden de la fase en la lista') }}</flux:description>
                                @error('order')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Current Phase Toggle --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Marcar como fase actual') }}
                                    <flux:tooltip content="{{ __('Solo puede haber una fase marcada como actual por convocatoria. Al marcar esta fase como actual, se desmarcará automáticamente la fase actual anterior.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:switch wire:model.live="is_current">
                                    {{ __('Es fase actual') }}
                                </flux:switch>
                                <flux:description>
                                    {{ __('Marca esta fase como la fase actual del proceso') }}
                                </flux:description>
                                @if($is_current && !$this->callPhase->is_current && $this->hasCurrentPhase())
                                    <flux:callout variant="warning" class="mt-2">
                                        <flux:callout.heading>{{ __('Atención') }}</flux:callout.heading>
                                        <flux:callout.text>
                                            {{ __('La fase ":name" está marcada como actual y será desmarcada automáticamente.', ['name' => $this->getCurrentPhaseName()]) }}
                                        </flux:callout.text>
                                    </flux:callout>
                                @endif
                                @error('is_current')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Info Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                            </div>
                            <div class="space-y-2 text-sm">
                                @if($this->callPhase->created_at)
                                    <div>
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Creada') }}:</span>
                                        <span class="ml-2 font-medium text-zinc-900 dark:text-white">
                                            {{ $this->callPhase->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @endif
                                @if($this->callPhase->updated_at && $this->callPhase->updated_at->ne($this->callPhase->created_at))
                                    <div>
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Actualizada') }}:</span>
                                        <span class="ml-2 font-medium text-zinc-900 dark:text-white">
                                            {{ $this->callPhase->updated_at->format('d/m/Y H:i') }}
                                        </span>
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
                                    href="{{ route('admin.calls.phases.index', $call) }}" 
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
    <x-ui.toast event="phase-updated" variant="success" />
    <x-ui.toast event="phase-date-overlap-warning" variant="warning" />
</div>
