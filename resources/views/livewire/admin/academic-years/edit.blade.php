<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Editar Año Académico') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Modifica la información del año académico') }}: <strong>{{ $academicYear->year }}</strong>
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.academic-years.index') }}" 
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
                ['label' => __('common.nav.academic_years'), 'href' => route('admin.academic-years.index'), 'icon' => 'calendar'],
                ['label' => $academicYear->year, 'href' => route('admin.academic-years.show', $academicYear), 'icon' => 'calendar'],
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
                            {{-- Year Field --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Año Académico') }} <span class="text-red-500">*</span>
                                    <flux:tooltip content="{{ __('Formato requerido: YYYY-YYYY (ejemplo: 2024-2025). Este formato identifica de forma única cada año académico.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input 
                                    wire:model.live.blur="year" 
                                    placeholder="Ej: 2024-2025"
                                    required
                                    autofocus
                                    maxlength="9"
                                />
                                <flux:description>{{ __('Formato: YYYY-YYYY (ejemplo: 2024-2025)') }}</flux:description>
                                @error('year')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- Start Date Field --}}
                            <flux:field>
                                <flux:label>{{ __('Fecha de Inicio') }} <span class="text-red-500">*</span></flux:label>
                                <flux:input 
                                    wire:model.live.blur="start_date" 
                                    type="date"
                                    required
                                />
                                <flux:description>{{ __('Fecha de inicio del año académico') }}</flux:description>
                                @error('start_date')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>

                            {{-- End Date Field --}}
                            <flux:field>
                                <flux:label>{{ __('Fecha de Fin') }} <span class="text-red-500">*</span></flux:label>
                                <flux:input 
                                    wire:model.live.blur="end_date" 
                                    type="date"
                                    required
                                    :min="$start_date ? $start_date : null"
                                />
                                <flux:description>{{ __('Fecha de fin del año académico (debe ser posterior a la fecha de inicio)') }}</flux:description>
                                @if($start_date && $end_date && $end_date <= $start_date)
                                    <flux:error>{{ __('La fecha de fin debe ser posterior a la fecha de inicio.') }}</flux:error>
                                @endif
                                @error('end_date')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:field>
                        </div>
                    </x-ui.card>

                    {{-- Relations Info Card --}}
                    @php
                        $callsCount = $academicYear->calls()->count();
                        $newsCount = $academicYear->newsPosts()->count();
                        $docsCount = $academicYear->documents()->count();
                        $hasRelations = $callsCount > 0 || $newsCount > 0 || $docsCount > 0;
                    @endphp
                    @if($hasRelations)
                        <x-ui.card>
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="sm">{{ __('Relaciones Existentes') }}</flux:heading>
                                    <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Este año académico tiene las siguientes relaciones activas:') }}
                                    </flux:text>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $callsCount }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Convocatorias') }}
                                        </div>
                                    </div>
                                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $newsCount }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Noticias') }}
                                        </div>
                                    </div>
                                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $docsCount }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Documentos') }}
                                        </div>
                                    </div>
                                </div>
                                <flux:callout variant="info" class="mt-2">
                                    <flux:callout.text>
                                        {{ __('Tenga en cuenta que cambiar el año académico puede afectar a estas relaciones.') }}
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

                            {{-- Current Year Toggle --}}
                            <flux:field>
                                <flux:label>
                                    {{ __('Marcar como año actual') }}
                                    <flux:tooltip content="{{ __('El año académico marcado como actual se utilizará por defecto en el sistema. Solo puede haber un año actual a la vez. Al marcar este año como actual, se desmarcará automáticamente el año actual anterior.') }}" position="top">
                                        <flux:icon name="information-circle" class="[:where(&)]:size-4 ml-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                                <flux:checkbox wire:model.live="is_current">
                                    {{ __('Marcar como año actual') }}
                                </flux:checkbox>
                                <flux:description>
                                    {{ __('Si marca este año como actual, se desmarcará automáticamente el año actual anterior.') }}
                                </flux:description>
                                @if($is_current && !$academicYear->is_current)
                                    @php
                                        $currentYear = \App\Models\AcademicYear::where('is_current', true)
                                            ->where('id', '!=', $academicYear->id)
                                            ->first();
                                    @endphp
                                    @if($currentYear)
                                        <flux:callout variant="warning" class="mt-2">
                                            <flux:callout.heading>{{ __('Atención') }}</flux:callout.heading>
                                            <flux:callout.text>
                                                {{ __('El año académico :year está marcado como actual y será desmarcado automáticamente.', ['year' => $currentYear->year]) }}
                                            </flux:callout.text>
                                        </flux:callout>
                                    @endif
                                @endif
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
                                    href="{{ route('admin.academic-years.show', $academicYear) }}" 
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
    <x-ui.toast event="academic-year-updated" variant="success" />
</div>
