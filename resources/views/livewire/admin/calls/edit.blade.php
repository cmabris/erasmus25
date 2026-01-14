<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Convocatoria') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información de la convocatoria') }}: <strong>{{ $call->title }}</strong>
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.calls.index') }}" 
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
                    {{-- Basic Information --}}
                    <x-ui.card>
                        <div class="mb-4">
                            <flux:heading size="sm">{{ __('Información Básica') }}</flux:heading>
                        </div>
                        <div class="space-y-6">
                            {{-- Program --}}
                            <flux:field>
                                <flux:label>{{ __('Programa') }} <span class="text-red-500">*</span></flux:label>
                                <select wire:model.live.blur="program_id" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" required>
                                    <option value="0">{{ __('Selecciona un programa') }}</option>
                                    @foreach($this->programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
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
                                    placeholder="{{ __('Ej: Convocatoria Movilidad Alumnado 2024-2025') }}"
                                    required
                                    autofocus
                                />
                                <flux:description>{{ __('Título descriptivo de la convocatoria') }}</flux:description>
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

                            {{-- Type and Modality --}}
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <flux:field>
                                    <flux:label>{{ __('Tipo') }} <span class="text-red-500">*</span></flux:label>
                                    <select wire:model.live.blur="type" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" required>
                                        <option value="alumnado">{{ __('Alumnado') }}</option>
                                        <option value="personal">{{ __('Personal') }}</option>
                                    </select>
                                    @error('type')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Modalidad') }} <span class="text-red-500">*</span></flux:label>
                                    <select wire:model.live.blur="modality" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" required>
                                        <option value="corta">{{ __('Corta') }}</option>
                                        <option value="larga">{{ __('Larga') }}</option>
                                    </select>
                                    @error('modality')
                                        <flux:error>{{ $message }}</flux:error>
                                    @enderror
                                </flux:field>
                            </div>

                            {{-- Number of Places --}}
                            <flux:field>
                                <flux:label>{{ __('Número de Plazas') }} <span class="text-red-500">*</span></flux:label>
                                <flux:input 
                                    wire:model.live.blur="number_of_places" 
                                    type="number"
                                    min="1"
                                    required
                                />
                                <flux:description>{{ __('Número total de plazas disponibles') }}</flux:description>
                                @error('number_of_places')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Destinations --}}
                    <x-ui.card>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="sm">{{ __('Destinos') }}</flux:heading>
                            <flux:button 
                                type="button"
                                wire:click="addDestination"
                                variant="ghost"
                                size="sm"
                                icon="plus"
                            >
                                {{ __('Añadir Destino') }}
                            </flux:button>
                        </div>
                        <div class="space-y-3">
                            @foreach($destinations as $index => $destination)
                                <div class="flex items-start gap-2">
                                    <flux:field class="flex-1">
                                        <flux:input 
                                            wire:model.live.blur="destinations.{{ $index }}"
                                            placeholder="{{ __('Ej: Francia, Alemania, Italia...') }}"
                                        />
                                        @error("destinations.{$index}")
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </flux:field>
                                    @if(count($destinations) > 1)
                                        <flux:button 
                                            type="button"
                                            wire:click="removeDestination({{ $index }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="trash"
                                            :label="__('Eliminar')"
                                        />
                                    @endif
                                </div>
                            @endforeach
                            @error('destinations')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </div>
                    </x-ui.card>

                    {{-- Dates --}}
                    <x-ui.card>
                        <div class="mb-4">
                            <flux:heading size="sm">{{ __('Fechas Estimadas') }}</flux:heading>
                        </div>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>{{ __('Fecha Inicio Estimada') }}</flux:label>
                                <flux:input 
                                    wire:model.live.blur="estimated_start_date" 
                                    type="date"
                                />
                                @error('estimated_start_date')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Fecha Fin Estimada') }}</flux:label>
                                <flux:input 
                                    wire:model.live.blur="estimated_end_date" 
                                    type="date"
                                    :min="$estimated_start_date ? $estimated_start_date : null"
                                />
                                @if($estimated_start_date && $estimated_end_date && $estimated_end_date <= $estimated_start_date)
                                    <flux:error>{{ __('La fecha de fin debe ser posterior a la fecha de inicio.') }}</flux:error>
                                @endif
                                @error('estimated_end_date')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Content Fields --}}
                    <x-ui.card>
                        <div class="mb-4">
                            <flux:heading size="sm">{{ __('Contenido') }}</flux:heading>
                        </div>
                        <div class="space-y-6">
                            <flux:field>
                                <flux:label>{{ __('Requisitos') }}</flux:label>
                                <flux:textarea 
                                    wire:model.blur="requirements" 
                                    rows="4"
                                    placeholder="{{ __('Describe los requisitos necesarios para participar...') }}"
                                />
                                @error('requirements')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Documentación') }}</flux:label>
                                <flux:textarea 
                                    wire:model.blur="documentation" 
                                    rows="4"
                                    placeholder="{{ __('Lista la documentación requerida...') }}"
                                />
                                @error('documentation')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Criterios de Selección') }}</flux:label>
                                <flux:textarea 
                                    wire:model.blur="selection_criteria" 
                                    rows="4"
                                    placeholder="{{ __('Describe los criterios de selección...') }}"
                                />
                                @error('selection_criteria')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Scoring Table --}}
                    <x-ui.card>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="sm">{{ __('Baremo de Evaluación') }}</flux:heading>
                            <flux:button 
                                type="button"
                                wire:click="addScoringItem"
                                variant="ghost"
                                size="sm"
                                icon="plus"
                            >
                                {{ __('Añadir Concepto') }}
                            </flux:button>
                        </div>
                        <div class="space-y-4">
                            @foreach($scoringTable as $index => $item)
                                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Concepto') }} #{{ is_numeric($index) ? ($index + 1) : ($loop->iteration) }}</span>
                                        @if(count($scoringTable) > 1)
                                            <flux:button 
                                                type="button"
                                                wire:click="removeScoringItem({{ $index }})"
                                                variant="ghost"
                                                size="sm"
                                                icon="trash"
                                                :label="__('Eliminar')"
                                            />
                                        @endif
                                    </div>
                                    <div class="space-y-3">
                                        <flux:field>
                                            <flux:label>{{ __('Concepto') }}</flux:label>
                                            <flux:input 
                                                wire:model.live.blur="scoringTable.{{ $index }}.concept"
                                                placeholder="{{ __('Ej: Expediente académico, Entrevista...') }}"
                                            />
                                        </flux:field>
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <flux:field>
                                                <flux:label>{{ __('Puntos Máximos') }}</flux:label>
                                                <flux:input 
                                                    wire:model.live.blur="scoringTable.{{ $index }}.max_points"
                                                    type="number"
                                                    min="0"
                                                    placeholder="0"
                                                />
                                            </flux:field>
                                            <flux:field>
                                                <flux:label>{{ __('Descripción') }}</flux:label>
                                                <flux:input 
                                                    wire:model.live.blur="scoringTable.{{ $index }}.description"
                                                    placeholder="{{ __('Descripción del concepto...') }}"
                                                />
                                            </flux:field>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @error('scoring_table')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </div>
                    </x-ui.card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Information Card --}}
                    <x-ui.card>
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="sm">{{ __('Información') }}</flux:heading>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('Creada') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $call->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($call->updated_at && $call->updated_at->ne($call->created_at))
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Actualizada') }}:</span>
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $call->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                                @if($call->creator)
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Creada por') }}:</span>
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $call->creator->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Status Card --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="sm">{{ __('Estado') }}</flux:heading>
                            </div>

                            <flux:field>
                                <flux:label>{{ __('Estado') }}</flux:label>
                                <select wire:model.live.blur="status" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                    <option value="borrador">{{ __('Borrador') }}</option>
                                    <option value="abierta">{{ __('Abierta') }}</option>
                                    <option value="cerrada">{{ __('Cerrada') }}</option>
                                    <option value="en_baremacion">{{ __('En Baremación') }}</option>
                                    <option value="resuelta">{{ __('Resuelta') }}</option>
                                    <option value="archivada">{{ __('Archivada') }}</option>
                                </select>
                                @error('status')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
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
                                        {{ __('common.actions.save') }}
                                    </span>
                                    <span wire:loading wire:target="update">
                                        {{ __('Guardando...') }}
                                    </span>
                                </flux:button>

                                <flux:button 
                                    type="button"
                                    href="{{ route('admin.calls.show', $call) }}" 
                                    variant="ghost"
                                    wire:navigate
                                    class="w-full"
                                >
                                    {{ __('common.actions.back') }}
                                </flux:button>

                                @can('delete', $call)
                                    <flux:button 
                                        type="button"
                                        href="{{ route('admin.calls.index') }}" 
                                        wire:click="$dispatch('open-modal', ['name' => 'delete-call', 'callId' => {{ $call->id }}])"
                                        variant="ghost"
                                        icon="trash"
                                        class="w-full text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
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

    {{-- Toast Notifications --}}
    <x-ui.toast event="call-updated" variant="success" />
</div>
